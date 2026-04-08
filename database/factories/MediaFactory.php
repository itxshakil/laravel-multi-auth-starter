<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Media;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Media>
 */
class MediaFactory extends Factory
{
    /**
     * @return array<string, UserFactory|string|int|null>
     */
    public function definition(): array
    {
        $fileName = fake()->word().'.jpg';

        return [
            'model_type' => User::class,
            'model_id' => User::factory(),
            'name' => fake()->word(),
            'file_name' => $fileName,
            'mime_type' => 'image/jpeg',
            'path' => 'uploads/'.$fileName,
            'disk' => 'public',
            'file_hash' => base64_encode(fake()->sha256()),
            'collection' => 'default',
            'order' => null,
            'size' => fake()->numberBetween(1024, 1024 * 1024),
        ];
    }
}
