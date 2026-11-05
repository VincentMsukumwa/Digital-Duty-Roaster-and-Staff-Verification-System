<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fingerprint_templates', function (Blueprint $table) {

            // One fingerprint mapping per staff member.
            $table->unique(
                'staff_id',
                'fingerprint_templates_staff_id_unique'
            );

            // One staff member per fingerprint slot on a device.
            $table->unique(
                ['device_id', 'fingerprint_id'],
                'fingerprint_templates_device_fingerprint_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('fingerprint_templates', function (Blueprint $table) {

            $table->dropUnique(
                'fingerprint_templates_staff_id_unique'
            );

            $table->dropUnique(
                'fingerprint_templates_device_fingerprint_unique'
            );
        });
    }
};
