<?php

namespace Tests\Feature;

use App\Models\ExchangeRequest;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthorizationPoliciesTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    protected User $admin;
    protected User $staff;
    protected User $customerA;
    protected User $customerB;
    protected Order $orderA;
    protected Order $orderB;
    protected ExchangeRequest $exchangeA;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'Admin Policy User',
            'email' => 'admin_policy@brandfootwear.com',
            'phone' => '01700999001',
            'password' => Hash::make('secret123'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->staff = User::create([
            'name' => 'Staff Policy User',
            'email' => 'staff_policy@brandfootwear.com',
            'phone' => '01700999002',
            'password' => Hash::make('secret123'),
            'role' => 'staff',
            'is_active' => true,
        ]);

        $this->customerA = User::create([
            'name' => 'Customer A',
            'email' => 'customera_policy@example.com',
            'phone' => '01700999003',
            'password' => Hash::make('secret123'),
            'role' => 'customer',
            'is_active' => true,
        ]);

        $this->customerB = User::create([
            'name' => 'Customer B',
            'email' => 'customerb_policy@example.com',
            'phone' => '01700999004',
            'password' => Hash::make('secret123'),
            'role' => 'customer',
            'is_active' => true,
        ]);

        $product = Product::where('slug', 'velvet-block-heel-pump')->firstOrFail();
        $variant = $product->variants()->firstOrFail();

        $this->orderA = Order::create([
            'order_number' => 'ORD-POL-A-' . uniqid(),
            'user_id' => $this->customerA->id,
            'customer_name' => $this->customerA->name,
            'customer_phone' => $this->customerA->phone,
            'customer_email' => $this->customerA->email,
            'shipping_division' => 'Dhaka',
            'shipping_district' => 'Dhaka',
            'shipping_thana' => 'Gulshan',
            'shipping_address' => 'Road 11, House 1',
            'subtotal' => 2500.00,
            'delivery_charge' => 70.00,
            'total' => 2570.00,
            'status' => 'pending',
            'payment_method' => 'cod',
            'payment_status' => 'pending',
        ]);

        $this->orderB = Order::create([
            'order_number' => 'ORD-POL-B-' . uniqid(),
            'user_id' => $this->customerB->id,
            'customer_name' => $this->customerB->name,
            'customer_phone' => $this->customerB->phone,
            'customer_email' => $this->customerB->email,
            'shipping_division' => 'Dhaka',
            'shipping_district' => 'Dhaka',
            'shipping_thana' => 'Banani',
            'shipping_address' => 'Road 2, House 15',
            'subtotal' => 2500.00,
            'delivery_charge' => 70.00,
            'total' => 2570.00,
            'status' => 'processing',
            'payment_method' => 'cod',
            'payment_status' => 'pending',
        ]);

        $itemA = OrderItem::create([
            'order_id' => $this->orderA->id,
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
            'product_name' => $product->name,
            'variant_details' => ['size' => '36', 'color' => 'Black'],
            'unit_price' => 2500.00,
            'quantity' => 1,
            'total_price' => 2500.00,
        ]);

        $this->exchangeA = ExchangeRequest::create([
            'user_id' => $this->customerA->id,
            'order_id' => $this->orderA->id,
            'order_item_id' => $itemA->id,
            'reason' => 'Size too tight',
            'status' => 'pending',
        ]);
    }

    public function test_order_policy_view_authorization(): void
    {
        // Customer A can view their own order
        $this->assertTrue(Gate::forUser($this->customerA)->allows('view', $this->orderA));

        // Customer A CANNOT view Customer B's order
        $this->assertTrue(Gate::forUser($this->customerA)->denies('view', $this->orderB));

        // Customer B can view their own order
        $this->assertTrue(Gate::forUser($this->customerB)->allows('view', $this->orderB));

        // Admin and Staff can view any order
        $this->assertTrue(Gate::forUser($this->admin)->allows('view', $this->orderA));
        $this->assertTrue(Gate::forUser($this->admin)->allows('view', $this->orderB));
        $this->assertTrue(Gate::forUser($this->staff)->allows('view', $this->orderA));
        $this->assertTrue(Gate::forUser($this->staff)->allows('view', $this->orderB));
    }

    public function test_order_policy_update_authorization(): void
    {
        // Customer can update their own order if status is 'pending'
        $this->assertEquals('pending', $this->orderA->status);
        $this->assertTrue(Gate::forUser($this->customerA)->allows('update', $this->orderA));

        // Customer cannot update order once it is 'processing'
        $this->assertEquals('processing', $this->orderB->status);
        $this->assertTrue(Gate::forUser($this->customerB)->denies('update', $this->orderB));

        // Customer A cannot update Customer B's order even if pending
        $orderBPending = Order::create([
            'order_number' => 'ORD-POL-BP-' . uniqid(),
            'user_id' => $this->customerB->id,
            'customer_name' => $this->customerB->name,
            'customer_phone' => $this->customerB->phone,
            'shipping_division' => 'Dhaka',
            'shipping_district' => 'Dhaka',
            'shipping_thana' => 'Mirpur',
            'shipping_address' => 'Mirpur 1',
            'subtotal' => 2000.00,
            'delivery_charge' => 70.00,
            'total' => 2070.00,
            'status' => 'pending',
            'payment_method' => 'cod',
            'payment_status' => 'pending',
        ]);
        $this->assertTrue(Gate::forUser($this->customerA)->denies('update', $orderBPending));

        // Admin and Staff can update orders in any status
        $this->assertTrue(Gate::forUser($this->admin)->allows('update', $this->orderB));
        $this->assertTrue(Gate::forUser($this->staff)->allows('update', $this->orderB));
    }

    public function test_order_policy_delete_authorization(): void
    {
        // Customers and Staff cannot delete orders
        $this->assertTrue(Gate::forUser($this->customerA)->denies('delete', $this->orderA));
        $this->assertTrue(Gate::forUser($this->staff)->denies('delete', $this->orderA));

        // Only Admin can delete orders
        $this->assertTrue(Gate::forUser($this->admin)->allows('delete', $this->orderA));
    }

    public function test_exchange_request_policy_view_authorization(): void
    {
        // Customer A can view their own exchange request
        $this->assertTrue(Gate::forUser($this->customerA)->allows('view', $this->exchangeA));

        // Customer B CANNOT view Customer A's exchange request
        $this->assertTrue(Gate::forUser($this->customerB)->denies('view', $this->exchangeA));

        // Admin and Staff can view any exchange request
        $this->assertTrue(Gate::forUser($this->admin)->allows('view', $this->exchangeA));
        $this->assertTrue(Gate::forUser($this->staff)->allows('view', $this->exchangeA));
    }

    public function test_exchange_request_policy_update_and_delete_authorization(): void
    {
        // Customer cannot update status of exchange request (only admin/staff can moderate)
        $this->assertTrue(Gate::forUser($this->customerA)->denies('update', $this->exchangeA));
        $this->assertTrue(Gate::forUser($this->admin)->allows('update', $this->exchangeA));
        $this->assertTrue(Gate::forUser($this->staff)->allows('update', $this->exchangeA));

        // Only Admin can delete exchange request
        $this->assertTrue(Gate::forUser($this->customerA)->denies('delete', $this->exchangeA));
        $this->assertTrue(Gate::forUser($this->staff)->denies('delete', $this->exchangeA));
        $this->assertTrue(Gate::forUser($this->admin)->allows('delete', $this->exchangeA));
    }
}
