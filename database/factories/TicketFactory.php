<?php
namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class TicketFactory extends Factory {
    public function definition(): array {
        $status = fake()->randomElement(['open','in_progress','closed']);
        return [
            'id'          => (string) \Illuminate\Support\Str::ulid(),
            'subject'     => fake()->sentence(),
            'body'        => fake()->paragraph(4),
            'status'      => $status,
            'category'    => fake()->randomElement([null,'billing','bug','feature','account','other']),
            'explanation' => fake()->boolean(40) ? fake()->sentence(10) : null,
            'confidence'  => fake()->boolean(40) ? fake()->randomFloat(2, 0.3, 0.98) : null,
            'note'        => fake()->boolean(35) ? fake()->sentence(12) : null,
        ];
    }
}