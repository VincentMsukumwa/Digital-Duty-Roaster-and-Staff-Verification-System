<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Device;
use App\Models\DeviceCommand;
use App\Models\FingerprintTemplate;
use App\Models\Staff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DeviceCommandController extends Controller
{
    /**
     * Start fingerprint enrollment for a staff member.
     */
    public function startEnrollment(Request $request)
    {
        $data = $request->validate([
            'device_id' => 'required|exists:devices,id',
            'staff_id'  => 'required|exists:staff,id',
        ]);

        return DB::transaction(function () use ($request, $data) {

            // Lock the staff row to prevent simultaneous duplicate requests.
            $staff = Staff::whereKey($data['staff_id'])
                ->lockForUpdate()
                ->firstOrFail();

            // Block enrollment for archived staff.
            if ($staff->trashed()) {
                return response()->json([
                    'result' => 'error',
                    'message' => 'Archived staff members cannot enroll fingerprints.',
                ], 403);
            }

            // A staff member can only have ONE fingerprint mapping.
            $existingFingerprint = FingerprintTemplate::where(
                'staff_id',
                $staff->id
            )->exists();

            if ($existingFingerprint) {
                return response()->json([
                    'result' => 'error',
                    'message' => 'This staff member already has a registered fingerprint. Duplicate enrollment and replacement are not allowed.',
                ], 409);
            }

            // Prevent another pending enrollment for this staff member.
            $staffHasPendingCommand = DeviceCommand::where(
                'staff_id',
                $staff->id
            )
                ->where('command', 'enroll')
                ->where('status', 'pending')
                ->exists();

            if ($staffHasPendingCommand) {
                return response()->json([
                    'result' => 'error',
                    'message' => 'This staff member already has a pending fingerprint enrollment.',
                ], 409);
            }

            // Prevent multiple pending commands on the same device.
            $existingCommand = DeviceCommand::where(
                'device_id',
                $data['device_id']
            )
                ->where('status', 'pending')
                ->exists();

            if ($existingCommand) {
                return response()->json([
                    'result' => 'error',
                    'message' => 'This device already has a pending command.',
                ], 409);
            }

            $command = DeviceCommand::create([
                'device_id' => $data['device_id'],
                'staff_id'  => $staff->id,
                'command'   => 'enroll',
                'status'    => 'pending',
            ]);

            AuditLog::create([
                'user_id'        => $request->user()?->id,
                'action'         => 'FINGERPRINT_ENROLLMENT_STARTED',
                'table_affected' => 'device_commands',
                'record_id'      => $command->id,
                'ip_address'     => $request->ip(),
            ]);

            return response()->json([
                'result' => 'success',
                'message' => 'Fingerprint enrollment command created.',
                'command' => [
                    'id'        => $command->id,
                    'device_id' => $command->device_id,
                    'staff_id'  => $command->staff_id,
                    'command'   => $command->command,
                    'status'    => $command->status,
                ],
            ], 201);
        });
    }

    /**
     * ESP32 asks whether there is a command waiting for it.
     */
    public function getCommand($deviceId)
    {
        $device = Device::findOrFail($deviceId);

        $command = DeviceCommand::where('device_id', $device->id)
            ->where('status', 'pending')
            ->orderBy('id')
            ->first();

        if (! $command) {
            return response()->json([
                'mode' => 'normal',
            ]);
        }

        return response()->json([
            'mode'       => $command->command,
            'command_id' => $command->id,
            'staff_id'   => $command->staff_id,
        ]);
    }

    /**
     * ESP32 reports the result of a fingerprint enrollment.
     */
    public function complete(Request $request, $device, $command)
    {
        $data = $request->validate([
            'status' => 'required|in:completed,failed',
            'staff_id' => 'required|exists:staff,id',
            'fingerprint_id' => 'required|integer|min:1',
        ]);

        return DB::transaction(function () use (
            $request,
            $device,
            $command,
            $data
        ) {

            // Lock the command so it cannot be completed twice.
            $deviceCommand = DeviceCommand::where('id', $command)
                ->where('device_id', $device)
                ->lockForUpdate()
                ->first();

            if (! $deviceCommand) {
                return response()->json([
                    'result' => 'error',
                    'message' => 'Device command not found.',
                ], 404);
            }

            if ($deviceCommand->command !== 'enroll') {
                return response()->json([
                    'result' => 'error',
                    'message' => 'This command is not an enrollment command.',
                ], 422);
            }

            if ($deviceCommand->status !== 'pending') {
                return response()->json([
                    'result' => 'error',
                    'message' => 'This enrollment command has already been processed.',
                ], 409);
            }

            if ((int) $deviceCommand->staff_id !== (int) $data['staff_id']) {
                return response()->json([
                    'result' => 'error',
                    'message' => 'Staff member does not match the enrollment command.',
                ], 422);
            }

            // Lock the staff row as an additional duplicate-enrollment safeguard.
            $staff = Staff::whereKey($data['staff_id'])
                ->lockForUpdate()
                ->first();

            if (! $staff || $staff->trashed()) {
                return response()->json([
                    'result' => 'error',
                    'message' => 'Staff member does not exist or has been archived.',
                ], 404);
            }

            // ENROLLMENT FAILED
            if ($data['status'] === 'failed') {

                $deviceCommand->update([
                    'status' => 'failed',
                ]);

                AuditLog::create([
                    'user_id'        => null,
                    'action'         => 'FINGERPRINT_ENROLLMENT_FAILED',
                    'table_affected' => 'device_commands',
                    'record_id'      => $deviceCommand->id,
                    'ip_address'     => $request->ip(),
                ]);

                return response()->json([
                    'result' => 'failed',
                    'message' => 'Fingerprint enrollment failed.',
                    'command_id' => $deviceCommand->id,
                ]);
            }

            // Check again before saving a successful enrollment.
            $existingFingerprint = FingerprintTemplate::where(
                'staff_id',
                $staff->id
            )->exists();

            if ($existingFingerprint) {
                return response()->json([
                    'result' => 'error',
                    'message' => 'This staff member already has a fingerprint. Duplicate enrollment and replacement are not allowed.',
                ], 409);
            }

            // Prevent overwriting a fingerprint slot already assigned to someone.
            $occupiedSlot = FingerprintTemplate::where(
                'device_id',
                $deviceCommand->device_id
            )
                ->where('fingerprint_id', $data['fingerprint_id'])
                ->exists();

            if ($occupiedSlot) {
                return response()->json([
                    'result' => 'error',
                    'message' => 'This fingerprint slot is already assigned. Choose an unused slot.',
                ], 409);
            }

            /*
             * The AS608 stores the actual fingerprint model
             * in its local memory.
             *
             * Laravel stores the mapping:
             * device + fingerprint slot -> staff member.
             *
             * Do not use updateOrCreate() here because
             * existing fingerprint mappings must never be overwritten.
             */

            $fingerprintTemplate = FingerprintTemplate::create([
                'device_id' => $deviceCommand->device_id,
                'staff_id' => $staff->id,
                'fingerprint_id' => $data['fingerprint_id'],
                'finger_position' => null,
                'enrolled_at' => now(),
                'template_data' => '',
            ]);

            // Mark enrollment as completed.
            $deviceCommand->update([
                'status' => 'completed',
            ]);

            // Record successful enrollment.
            AuditLog::create([
                'user_id'        => null,
                'action'         => 'FINGERPRINT_ENROLLMENT_COMPLETED',
                'table_affected' => 'fingerprint_templates',
                'record_id'      => $fingerprintTemplate->id,
                'ip_address'     => $request->ip(),
            ]);

            return response()->json([
                'result' => 'success',
                'message' => 'Fingerprint enrollment completed.',
                'command_id' => $deviceCommand->id,
                'staff_id' => $staff->id,
                'fingerprint_id' => $data['fingerprint_id'],
            ]);
        });
    }
}
