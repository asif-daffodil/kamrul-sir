<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FilamentAdminTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    /**
     * Test unauthenticated guests are redirected to admin login.
     */
    public function test_guests_cannot_access_admin_panel(): void
    {
        $response = $this->get('/admin');

        $response->assertRedirect('/admin/login');
    }

    /**
     * Test customers cannot access the Filament admin panel.
     */
    public function test_customers_are_forbidden_from_admin_panel(): void
    {
        $customer = User::where('role', 'customer')->first();
        $this->assertNotNull($customer);

        $response = $this->actingAs($customer)->get('/admin');

        $response->assertStatus(403);
    }

    /**
     * Test admin can access the dashboard.
     */
    public function test_admin_can_access_dashboard(): void
    {
        $admin = User::where('role', 'admin')->first();
        $this->assertNotNull($admin);

        $response = $this->actingAs($admin)->get('/admin');

        $response->assertStatus(200);
        $response->assertSeeText("Today's Orders");
        $response->assertSeeText("Today's Revenue");
    }

    /**
     * Test staff can access the dashboard.
     */
    public function test_staff_can_access_dashboard(): void
    {
        $staff = User::where('role', 'staff')->first();
        $this->assertNotNull($staff);

        $response = $this->actingAs($staff)->get('/admin');

        $response->assertStatus(200);
    }

    /**
     * Test admin can view products resource.
     */
    public function test_admin_can_view_products_resource(): void
    {
        $admin = User::where('role', 'admin')->first();

        $response = $this->actingAs($admin)->get('/admin/products');

        $response->assertStatus(200);
    }

    /**
     * Test admin can view orders resource.
     */
    public function test_admin_can_view_orders_resource(): void
    {
        $admin = User::where('role', 'admin')->first();

        $response = $this->actingAs($admin)->get('/admin/orders');

        $response->assertStatus(200);
    }

    /**
     * Test printable invoice view.
     */
    public function test_admin_can_view_printable_invoice(): void
    {
        $admin = User::where('role', 'admin')->first();
        $order = Order::with('items')->first();
        $this->assertNotNull($order);

        $response = $this->actingAs($admin)->get(route('admin.orders.invoice', $order));

        $response->assertStatus(200);
        $response->assertSee($order->order_number);
        $response->assertSeeText('INVOICE');
        $response->assertSeeText($order->customer_name);
    }

    /**
     * Test admin can access settings page.
     */
    public function test_admin_can_access_settings_page(): void
    {
        $admin = User::where('role', 'admin')->first();

        $response = $this->actingAs($admin)->get('/admin/manage-settings');

        $response->assertStatus(200);
        $response->assertSeeText('Delivery Fees');
    }

    /**
     * Test admin can view reviews moderation resource.
     */
    public function test_admin_can_access_reviews_moderation(): void
    {
        $admin = User::where('role', 'admin')->first();

        $response = $this->actingAs($admin)->get('/admin/reviews');

        $response->assertStatus(200);
    }
}
