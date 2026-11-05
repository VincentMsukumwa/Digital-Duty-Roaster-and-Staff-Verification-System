<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Notifications for the authenticated user, most recent first.
     */
    public function index(Request $request)
    {
        return Notification::where('user_id', $request->user()->id)
            ->orderByDesc('created_at')
            ->paginate(20);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
            'title'   => 'required|string|max:150',
            'message' => 'nullable|string',
        ]);

        $data['is_read'] = false;

        return response()->json(Notification::create($data), 201);
    }

    public function show(Notification $notification)
    {
        return $notification;
    }

    public function markAsRead(Notification $notification)
    {
        $notification->update(['is_read' => true]);

        return $notification;
    }

    public function destroy(Notification $notification)
    {
        $notification->delete();

        return response()->json(null, 204);
    }
}
