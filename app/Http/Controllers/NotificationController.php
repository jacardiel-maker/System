<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = auth()->user()->notifications()->paginate(20);
        return view('notifications.index', compact('notifications'));
    }

    public function show($id)
    {
        $notification = auth()->user()->notifications()->findOrFail($id);
        $notification->markAsRead();
        return view('notifications.show', compact('notification'));
    }

    public function destroy($id)
    {
        $notification = auth()->user()->notifications()->findOrFail($id);
        $notification->delete();
        return redirect()->route('notifications.index')->with('success', 'Notification deleted.');
    }

    public function markAllRead()
    {
        auth()->user()->unreadNotifications->markAsRead();
        return back()->with('success', 'All notifications marked as read.');
    }

    public function clearAll()
    {
        auth()->user()->notifications()->delete();
        return redirect()->route('notifications.index')->with('success', 'All notifications cleared.');
    }
}