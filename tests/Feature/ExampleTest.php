<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_models_have_uuid_and_product_has_sku(): void
    {
        $this->seed(\Database\Seeders\ShopSeeder::class);

        $product = Product::first();

        $this->assertNotNull($product->uuid);
        $this->assertNotNull($product->sku);
    }
}
