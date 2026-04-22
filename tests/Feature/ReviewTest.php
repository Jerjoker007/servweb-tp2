<?php

namespace Tests\Feature;

use App\Models\Review;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Rental;
use App\Models\Role;
use App\Models\Category;
use App\Models\Equipment;
use Laravel\Sanctum\Sanctum;

class ReviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_review_createReview()
    {
        $role = Role::create(["name" => "user"]);
        $user = User::factory()->create([
            'first_name' => 'Joe',
            'last_name' => 'Bob',
            'email' => 'joe.bob@example.com',
            'phone' => '1234567890',
            'role_id' => $role->id,
            'login' => 'joebob',
            'password' => bcrypt('oldpassword123'),
        ]);

        $category = Category::create(['name' => 'Sports']);
        $equipment = Equipment::create([
            'name' => 'Kayak',
            'description' => 'Test equipment',
            'daily_price' => 10,
            'category_id' => $category->id,
        ]);
        $rental = Rental::create([
            'user_id' => $user->id,
            'equipment_id' => $equipment->id,
            'start_date' => '2024-01-01',
            'end_date' => '2024-01-05',
            'total_price' => 10,
        ]);

        $this->actingAs($user)->postJson('/api/reviews', [
            'rental_id' => $rental->id,
            'rating' => 4,
            'comment' => 'Great rental experience!',
        ])->assertStatus(201);
    }

    public function test_review_createReview_userNotAuthentificated()
    {
        $this->postJson('/api/reviews', [
            'rental_id' => 5,
            'rating' => 4,
            'comment' => 'Great rental experience!',
        ])->assertStatus(401);
    }

    public function test_review_createReview_alreadyExists()
    {
        $role = Role::create(["name" => "user"]);
        $user = User::factory()->create([
            'first_name' => 'Joe',
            'last_name' => 'Bob',
            'email' => 'joe.bob@example.com',
            'phone' => '1234567890',
            'role_id' => $role->id,
            'login' => 'joebob',
            'password' => bcrypt('oldpassword123'),
        ]);

        $category = Category::create(['name' => 'Sports']);
        $equipment = Equipment::create([
            'name' => 'Kayak',
            'description' => 'Test equipment',
            'daily_price' => 10,
            'category_id' => $category->id,
        ]);
        $rental = Rental::create([
            'user_id' => $user->id,
            'equipment_id' => $equipment->id,
            'start_date' => '2024-01-01',
            'end_date' => '2027-01-05',
            'total_price' => 10,
        ]);

        Review::create([
            'user_id' => $user->id,
            'rental_id' => $rental->id,
            'rating' => 5,
            'comment' => 'Excellent rental experience!',
        ]);

        $this->actingAs($user)->postJson('/api/reviews', [
            'rental_id' => $rental->id,
            'rating' => 4,
            'comment' => 'Great rental experience!',
        ])->assertStatus(409);
    }

    public function test_review_createReview_rentalNotFound()
    {
        $role = Role::create(["name" => "user"]);
        $user = User::factory()->create([
            'first_name' => 'Joe',
            'last_name' => 'Bob',
            'email' => 'joe.bob@example.com',
            'phone' => '1234567890',
            'role_id' => $role->id,
            'login' => 'joebob',
            'password' => bcrypt('oldpassword123'),
        ]);

        $this->actingAs($user)->postJson('/api/reviews', [
            'rental_id' => 5,
            'rating' => 4,
            'comment' => 'Great rental experience!',
        ])->assertStatus(422);
    }

    public function test_review_throttle()
    {
        $role = Role::create(["name" => "user"]);
        $user = User::factory()->create([
            'first_name' => 'Joe',
            'last_name' => 'Bob',
            'email' => 'joe.bob@example.com',
            'phone' => '1234567890',
            'role_id' => $role->id,
            'login' => 'joebob',
            'password' => bcrypt('oldpassword123'),
        ]);

        $category = Category::create(['name' => 'Sports']);

        for ($i = 0; $i < 60; $i++) {
            $equipment = Equipment::create([
                'name' => 'Kayak '.$i,
                'description' => 'Test equipment '.$i,
                'daily_price' => 10,
                'category_id' => $category->id,
            ]);

            $rental = Rental::create([
                'user_id' => $user->id,
                'equipment_id' => $equipment->id,
                'start_date' => '2024-01-01',
                'end_date' => '2024-01-05',
                'total_price' => 10,
            ]);

            $response = $this->actingAs($user)->postJson('/api/reviews', [
                'rental_id' => $rental->id,
                'rating' => 4,
                'comment' => 'Great rental experience!',
            ]);
            $response->assertStatus(201);
        }

        $response = $this->actingAs($user)->postJson('/api/reviews', [
            'rental_id' => $rental->id,
            'rating' => 4,
            'comment' => 'Great rental experience!',
        ]);
        $response->assertStatus(429);
    }

    public function test_review_forbidden()
    {
        $this->seed();

        $role = Role::create(["name" => "user"]);
        $user = User::factory()->create([
            'first_name' => 'Joe',
            'last_name' => 'Bob',
            'email' => 'joe.bob@example.com',
            'phone' => '1234567890',
            'role_id' => $role->id,
            'login' => 'joebob',
            'password' => bcrypt('oldpassword123'),
        ]);

        Sanctum::actingAs($user, ['*']);

        $response = $this->postJson('/api/reviews', [
            'rental_id' => 1,
            'rating' => 4,
            'comment' => 'Great rental experience!',
        ]);

        $response->assertStatus(403);
    }

}