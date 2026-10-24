<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Waiter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    private function waiter(): Waiter
    {
        $user = User::factory()->create(['role' => 'waiter']);

        return Waiter::create([
            'user_id' => $user->id,
            'phone' => '09123456789',
            'status' => 'active',
        ]);
    }

    public function test_waiter_profile_page_is_displayed(): void
    {
        $waiter = $this->waiter();

        $response = $this->actingAs($waiter->user)->get(route('waiters.my-profile'));

        $response->assertOk();
        $response->assertViewIs('waiters.my-profile');
        $response->assertViewHas('waiter');
    }

    public function test_non_waiter_cannot_open_waiter_profile(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get(route('waiters.my-profile'));

        $response->assertForbidden();
    }

    public function test_waiter_can_change_password(): void
    {
        $waiter = $this->waiter();

        $response = $this->actingAs($waiter->user)
            ->from(route('waiters.my-profile'))
            ->post(route('waiters.change-password'), [
                'current_password' => 'password',
                'new_password' => 'new-password',
                'new_password_confirmation' => 'new-password',
            ]);

        $response->assertRedirect(route('waiters.my-profile'));
        $response->assertSessionHas('password_success');
        $this->assertTrue(Hash::check('new-password', $waiter->user->fresh()->password));
    }

    public function test_waiter_password_is_not_changed_with_wrong_current_password(): void
    {
        $waiter = $this->waiter();

        $response = $this->actingAs($waiter->user)
            ->from(route('waiters.my-profile'))
            ->post(route('waiters.change-password'), [
                'current_password' => 'wrong-password',
                'new_password' => 'new-password',
                'new_password_confirmation' => 'new-password',
            ]);

        $response->assertRedirect(route('waiters.my-profile'));
        $response->assertSessionHas('password_error');
        $this->assertTrue(Hash::check('password', $waiter->user->fresh()->password));
    }

    public function test_owner_account_page_is_displayed(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);

        $response = $this->actingAs($owner)->get(route('owner.account'));

        $response->assertOk();
        $response->assertViewIs('owner.account');
    }

    public function test_owner_can_change_password(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);

        $response = $this->actingAs($owner)
            ->from(route('owner.account'))
            ->post(route('owner.change-password'), [
                'current_password' => 'password',
                'new_password' => 'new-password',
                'new_password_confirmation' => 'new-password',
            ]);

        $response->assertRedirect(route('owner.account'));
        $response->assertSessionHas('password_success');
        $this->assertTrue(Hash::check('new-password', $owner->fresh()->password));
    }

    public function test_non_owner_cannot_open_owner_account(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get(route('owner.account'));

        $response->assertForbidden();
    }
}
