<?php

namespace Database\Seeders;

use App\Models\User;
use App\Modules\Cart\Models\Cart;
use App\Modules\Cart\Models\CartItem;
use App\Modules\Service\Models\Service;
use App\Modules\Package\Models\Package;
use App\Core\Domain\Enums\CartItemType;
use Illuminate\Database\Seeder;

class CartSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $demoUser = User::where('email', 'demo@ajeer.app')->first();
        
        if (!$demoUser) {
            $this->command->error('Demo user not found. Please run UserSeeder first.');
            return;
        }

        $services = Service::take(3)->get();
        $packages = Package::take(2)->get();

        if ($services->isEmpty()) {
            $this->command->error('No services found. Please run ServiceSeeder first.');
            return;
        }

        // Create cart for demo user
        $cart = Cart::create([
            'user_id' => $demoUser->id,
        ]);

        // Add a service to cart
        if ($services->isNotEmpty()) {
            CartItem::create([
                'cart_id' => $cart->id,
                'item_type' => CartItemType::SERVICE,
                'item_id' => $services->first()->id,
                'quantity' => 1,
            ]);
        }

        // Add a package to cart if available
        if ($packages->isNotEmpty()) {
            CartItem::create([
                'cart_id' => $cart->id,
                'item_type' => CartItemType::PACKAGE,
                'item_id' => $packages->first()->id,
                'quantity' => 1,
            ]);
        }

        // Create carts for some other users
        $otherUsers = User::where('email', '!=', 'demo@ajeer.app')->limit(3)->get();
        
        foreach ($otherUsers as $user) {
            $userCart = Cart::create([
                'user_id' => $user->id,
            ]);

            // Add random items to cart
            if ($services->isNotEmpty() && rand(0, 1)) {
                CartItem::create([
                    'cart_id' => $userCart->id,
                    'item_type' => CartItemType::SERVICE,
                    'item_id' => $services->random()->id,
                    'quantity' => rand(1, 2),
                ]);
            }

            if ($packages->isNotEmpty() && rand(0, 1)) {
                CartItem::create([
                    'cart_id' => $userCart->id,
                    'item_type' => CartItemType::PACKAGE,
                    'item_id' => $packages->random()->id,
                    'quantity' => 1,
                ]);
            }
        }

        $this->command->info('Carts seeded successfully.');
    }
}
