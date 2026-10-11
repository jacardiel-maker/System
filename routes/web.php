<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\WaiterController;
use App\Http\Controllers\EquipmentCategoryController;
use App\Http\Controllers\EquipmentItemController;
use App\Http\Controllers\EventEquipmentController;
use App\Http\Controllers\EquipmentLogController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ReportController;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/api/provinces/{regionId}', [WaiterController::class, 'getProvinces'])->name('api.provinces');
Route::get('/api/cities/{provinceId}', [WaiterController::class, 'getCities'])->name('api.cities');
Route::get('/api/barangays/{cityId}', [WaiterController::class, 'getBarangays'])->name('api.barangays');

Route::middleware(['auth'])->group(function () {

    // ==================== DASHBOARD (both roles) ====================
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ==================== WAITER-ONLY: MY PROFILE ====================
    Route::get('/my-profile', function () {
        $waiter = auth()->user()->waiter;
        if (!$waiter) {
            abort(403, 'Only waiters have a profile.');
        }
        $waiter->load([
            'user',
            'region',
            'province',
            'city',
            'barangay',
            'events',
            'assignedEquipment.event',
            'assignedEquipment.equipmentItem',
        ]);
        return view('waiters.my-profile', compact('waiter'));
    })->name('waiters.my-profile');

    // ==================== NOTIFICATIONS (both roles) ====================
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/{id}', [NotificationController::class, 'show'])->name('notifications.show');
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy'])->name('notifications.destroy');
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllRead'])->name('notifications.mark-all-read');
    Route::post('/notifications/clear-all', [NotificationController::class, 'clearAll'])->name('notifications.clear-all');

    // ==================== WAITER-ONLY: MY EVENT DETAILS ====================
    Route::get('/my-events/{event}', function (\App\Models\Event $event) {
        $waiter = auth()->user()->waiter;
        if (!$waiter) {
            abort(403, 'Only waiters can view this page.');
        }

        $isAssigned = $event->waiters()->where('waiter_id', $waiter->id)->exists();
        if (!$isAssigned) {
            abort(403, 'You are not assigned to this event.');
        }

        $event->load(['waiters.user', 'equipment.equipmentItem.category', 'equipment.assignedWaiter.user', 'creator']);

        $myEquipment = $event->equipment()->where('assigned_waiter_id', $waiter->id)->with('equipmentItem')->get();

        $myAssignment = $event->waiters()->where('waiter_id', $waiter->id)->first();

        return view('waiters.event-detail', compact('event', 'myEquipment', 'myAssignment'));
    })->name('waiters.my-event');

    // Waiter change own password
    Route::post('/my-profile/change-password', function (Request $request) {
        $waiter = auth()->user()->waiter;
        if (!$waiter) {
            abort(403, 'Only waiters can change their password here.');
        }

        $validated = $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:6|confirmed',
        ]);

        if (!\Illuminate\Support\Facades\Hash::check($validated['current_password'], auth()->user()->password)) {
            return back()->with('password_error', 'Current password is incorrect.');
        }

        auth()->user()->update([
            'password' => \Illuminate\Support\Facades\Hash::make($validated['new_password']),
        ]);

        return back()->with('password_success', 'Password changed successfully!');
    })->name('waiters.change-password');

    // Waiter change own email
    Route::put('/my-profile/email', function (Request $request) {
        if (!auth()->user()->waiter) {
            abort(403, 'Only waiters can change their email here.');
        }
        return app(WaiterController::class)->updateMyEmail($request);
    })->name('waiters.update-email');

    // ==================== ADMIN-ONLY ROUTES ====================
    Route::group([], function () {

        // Events
        Route::get('/events', function (Request $request) {
            if (!auth()->user()->isAdmin())
                abort(403, 'Admin access only.');
            return app(EventController::class)->index($request);
        })->name('events.index');
        Route::get('/events/create', function () {
            if (!auth()->user()->isAdmin())
                abort(403, 'Admin access only.');
            return app(EventController::class)->create();
        })->name('events.create');
        Route::post('/events', function (Request $request) {
            if (!auth()->user()->isAdmin())
                abort(403, 'Admin access only.');
            return app(EventController::class)->store($request);
        })->name('events.store');
        Route::get('/events/{event}', function (\App\Models\Event $event) {
            if (!auth()->user()->isAdmin())
                abort(403, 'Admin access only.');
            return app(EventController::class)->show($event);
        })->name('events.show');
        Route::get('/events/{event}/edit', function (\App\Models\Event $event) {
            if (!auth()->user()->isAdmin())
                abort(403, 'Admin access only.');
            return app(EventController::class)->edit($event);
        })->name('events.edit');
        Route::put('/events/{event}', function (Request $request, \App\Models\Event $event) {
            if (!auth()->user()->isAdmin())
                abort(403, 'Admin access only.');
            return app(EventController::class)->update($request, $event);
        })->name('events.update');
        Route::delete('/events/{event}', function (\App\Models\Event $event) {
            if (!auth()->user()->isAdmin())
                abort(403, 'Admin access only.');
            return app(EventController::class)->destroy($event);
        })->name('events.destroy');
        Route::post('/events/{event}/confirm', function (\App\Models\Event $event, Request $request) {
            return app(EventController::class)->confirmParticipation($event, $request);
        })->name('events.confirm');

        // Waiters (admin manages staff)
        Route::get('/waiters', function () {
            if (!auth()->user()->isAdmin())
                abort(403, 'Admin access only.');
            return app(WaiterController::class)->index(request());
        })->name('waiters.index');
        Route::get('/waiters/create', function () {
            if (!auth()->user()->isAdmin())
                abort(403, 'Admin access only.');
            return app(WaiterController::class)->create();
        })->name('waiters.create');
        Route::post('/waiters', function (Request $request) {
            if (!auth()->user()->isAdmin())
                abort(403, 'Admin access only.');
            return app(WaiterController::class)->store($request);
        })->name('waiters.store');
        Route::get('/waiters/{waiter}', function (\App\Models\Waiter $waiter) {
            if (!auth()->user()->isAdmin())
                abort(403, 'Admin access only.');
            return app(WaiterController::class)->show($waiter);
        })->name('waiters.show');
        Route::get('/waiters/{waiter}/edit', function (\App\Models\Waiter $waiter) {
            if (!auth()->user()->isAdmin())
                abort(403, 'Admin access only.');
            return app(WaiterController::class)->edit($waiter);
        })->name('waiters.edit');
        Route::put('/waiters/{waiter}', function (Request $request, \App\Models\Waiter $waiter) {
            if (!auth()->user()->isAdmin())
                abort(403, 'Admin access only.');
            return app(WaiterController::class)->update($request, $waiter);
        })->name('waiters.update');
        Route::delete('/waiters/{waiter}', function (\App\Models\Waiter $waiter) {
            if (!auth()->user()->isAdmin())
                abort(403, 'Admin access only.');
            return app(WaiterController::class)->destroy($waiter);
        })->name('waiters.destroy');

        // Equipment Categories
        Route::get('/equipment-categories', function (Request $request) {
            if (!auth()->user()->isAdmin())
                abort(403);
            return app(EquipmentCategoryController::class)->index($request);
        })->name('equipment-categories.index');
        Route::get('/equipment-categories/create', function () {
            if (!auth()->user()->isAdmin())
                abort(403);
            return app(EquipmentCategoryController::class)->create();
        })->name('equipment-categories.create');
        Route::post('/equipment-categories', function (Request $request) {
            if (!auth()->user()->isAdmin())
                abort(403);
            return app(EquipmentCategoryController::class)->store($request);
        })->name('equipment-categories.store');
        Route::get('/equipment-categories/{equipmentCategory}', function (\App\Models\EquipmentCategory $equipmentCategory) {
            if (!auth()->user()->isAdmin())
                abort(403);
            return app(EquipmentCategoryController::class)->show($equipmentCategory);
        })->name('equipment-categories.show');
        Route::get('/equipment-categories/{equipmentCategory}/edit', function (\App\Models\EquipmentCategory $equipmentCategory) {
            if (!auth()->user()->isAdmin())
                abort(403);
            return app(EquipmentCategoryController::class)->edit($equipmentCategory);
        })->name('equipment-categories.edit');
        Route::put('/equipment-categories/{equipmentCategory}', function (Request $request, \App\Models\EquipmentCategory $equipmentCategory) {
            if (!auth()->user()->isAdmin())
                abort(403);
            return app(EquipmentCategoryController::class)->update($request, $equipmentCategory);
        })->name('equipment-categories.update');
        Route::delete('/equipment-categories/{equipmentCategory}', function (\App\Models\EquipmentCategory $equipmentCategory) {
            if (!auth()->user()->isAdmin())
                abort(403);
            return app(EquipmentCategoryController::class)->destroy($equipmentCategory);
        })->name('equipment-categories.destroy');

        // Equipment Items
        Route::get('/equipment-items', function (Request $request) {
            if (!auth()->user()->isAdmin())
                abort(403);
            return app(EquipmentItemController::class)->index($request);
        })->name('equipment-items.index');
        Route::get('/equipment-items/create', function () {
            if (!auth()->user()->isAdmin())
                abort(403);
            return app(EquipmentItemController::class)->create();
        })->name('equipment-items.create');
        Route::post('/equipment-items', function (Request $request) {
            if (!auth()->user()->isAdmin())
                abort(403);
            return app(EquipmentItemController::class)->store($request);
        })->name('equipment-items.store');
        Route::get('/equipment-items/{equipmentItem}', function (\App\Models\EquipmentItem $equipmentItem) {
            if (!auth()->user()->isAdmin())
                abort(403);
            return app(EquipmentItemController::class)->show($equipmentItem);
        })->name('equipment-items.show');
        Route::get('/equipment-items/{equipmentItem}/edit', function (\App\Models\EquipmentItem $equipmentItem) {
            if (!auth()->user()->isAdmin())
                abort(403);
            return app(EquipmentItemController::class)->edit($equipmentItem);
        })->name('equipment-items.edit');
        Route::put('/equipment-items/{equipmentItem}', function (Request $request, \App\Models\EquipmentItem $equipmentItem) {
            if (!auth()->user()->isAdmin())
                abort(403);
            return app(EquipmentItemController::class)->update($request, $equipmentItem);
        })->name('equipment-items.update');
        Route::delete('/equipment-items/{equipmentItem}', function (\App\Models\EquipmentItem $equipmentItem) {
            if (!auth()->user()->isAdmin())
                abort(403);
            return app(EquipmentItemController::class)->destroy($equipmentItem);
        })->name('equipment-items.destroy');

        // Event Equipment Allocations
        Route::get('/event-equipment', function (Request $request) {
            if (!auth()->user()->isAdmin())
                abort(403);
            return app(EventEquipmentController::class)->index($request);
        })->name('event-equipment.index');
        Route::get('/event-equipment/create', function () {
            if (!auth()->user()->isAdmin())
                abort(403);
            return app(EventEquipmentController::class)->create();
        })->name('event-equipment.create');
        Route::post('/event-equipment', function (Request $request) {
            if (!auth()->user()->isAdmin())
                abort(403);
            return app(EventEquipmentController::class)->store($request);
        })->name('event-equipment.store');
        Route::get('/event-equipment/{eventEquipment}', function (\App\Models\EventEquipment $eventEquipment) {
            if (!auth()->user()->isAdmin())
                abort(403);
            return app(EventEquipmentController::class)->show($eventEquipment);
        })->name('event-equipment.show');
        Route::get('/event-equipment/{eventEquipment}/edit', function (\App\Models\EventEquipment $eventEquipment) {
            if (!auth()->user()->isAdmin())
                abort(403);
            return app(EventEquipmentController::class)->edit($eventEquipment);
        })->name('event-equipment.edit');
        Route::put('/event-equipment/{eventEquipment}', function (Request $request, \App\Models\EventEquipment $eventEquipment) {
            if (!auth()->user()->isAdmin())
                abort(403);
            return app(EventEquipmentController::class)->update($request, $eventEquipment);
        })->name('event-equipment.update');
        Route::delete('/event-equipment/{eventEquipment}', function (\App\Models\EventEquipment $eventEquipment) {
            if (!auth()->user()->isAdmin())
                abort(403);
            return app(EventEquipmentController::class)->destroy($eventEquipment);
        })->name('event-equipment.destroy');
        Route::post('/event-equipment/{eventEquipment}/return', function (Request $request, \App\Models\EventEquipment $eventEquipment) {
            if (!auth()->user()->isAdmin())
                abort(403);
            return app(EventEquipmentController::class)->returnEquipment($request, $eventEquipment);
        })->name('event-equipment.return');

        // Equipment Logs (admin view)
        Route::get('/equipment-logs', function (Request $request) {
            if (!auth()->user()->isAdmin())
                abort(403);
            return app(EquipmentLogController::class)->index($request);
        })->name('equipment-logs.index');
        Route::get('/equipment-logs/{equipmentLog}', function (\App\Models\EquipmentLog $equipmentLog) {
            if (!auth()->user()->isAdmin())
                abort(403);
            return app(EquipmentLogController::class)->show($equipmentLog);
        })->name('equipment-logs.show');

        // Reports (admin only)
        Route::get('/reports', function () {
            if (!auth()->user()->isAdmin())
                abort(403);
            return app(ReportController::class)->index();
        })->name('reports.index');
        Route::get('/reports/events', function (Request $request) {
            if (!auth()->user()->isAdmin())
                abort(403);
            return app(ReportController::class)->events($request);
        })->name('reports.events');
        Route::get('/reports/equipment', function (Request $request) {
            if (!auth()->user()->isAdmin())
                abort(403);
            return app(ReportController::class)->equipmentUsage($request);
        })->name('reports.equipment');
        Route::get('/reports/accountability', function (Request $request) {
            if (!auth()->user()->isAdmin())
                abort(403);
            return app(ReportController::class)->accountability($request);
        })->name('reports.accountability');
        Route::get('/reports/equipment-logs', function (Request $request) {
            if (!auth()->user()->isAdmin())
                abort(403);
            return app(ReportController::class)->equipmentLogs($request);
        })->name('reports.equipment-logs');

        // Check-in and Check-out
        Route::post('/events/{event}/waiters/{waiter}/time', function (Request $request, \App\Models\Event $event, \App\Models\Waiter $waiter) {
            if (!auth()->user()->isAdmin())
                abort(403);

            $validated = $request->validate([
                'time_in' => 'nullable|date',
                'time_out' => 'nullable|date|after_or_equal:time_in',
            ]);

            $event->waiters()->updateExistingPivot($waiter->id, [
                'time_in' => $validated['time_in'] ?? null,
                'time_out' => $validated['time_out'] ?? null,
            ]);

            return back()->with('success', 'Attendance updated for ' . $waiter->user->name);
        })->name('events.waiter.time');

    });

});

require __DIR__ . '/auth.php';