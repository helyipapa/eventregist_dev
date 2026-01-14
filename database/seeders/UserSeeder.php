<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //Admin
        User::factory()->create([
            'name'=>'admin',
            'email'=>'admin@events.hu',
            'password'=>Hash::make('admin123'),
            'is_admin' => true,
        ]);

        //Teszt
        User::factory()->create([
            'name'=>'test',
            'email'=>'test@events.hu',
            'password'=>Hash::make('test123'),
        ]);  
        
        User::factory()->count(10)->create();

        $this->command->info('UserSeeder: 12 felahsználó létrehozva (1 admin, 1 test, 10 random)');
    }
}
