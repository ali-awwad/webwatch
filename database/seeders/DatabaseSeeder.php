<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Ali Awwad',
            'email' => 'a.awwad@outlook.com',
            'password' => Hash::make('my@C0mplexP@ssw0rd@2231'),
        ]);
    }
}
