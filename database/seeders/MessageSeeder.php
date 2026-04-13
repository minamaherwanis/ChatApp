<?php

namespace Database\Seeders;

use App\Models\Message;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MessageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
  public function run(): void
    {
        $users = [10, 4];

        for ($i = 0; $i < 50; $i++) {
            Message::create([
                'chat_id' => 3,
                'sender_id' => $users[$i % 2], // تبديل بين 10 و 4
                'content' => fake()->sentence(),
                'is_read' => fake()->boolean(),
                'created_at' => Carbon::now()->subMinutes(50 - $i),
                'updated_at' => now(),
            ]);
        }
    }
}
