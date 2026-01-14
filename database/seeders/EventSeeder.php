<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Event;

class EventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //fix események:
        $events = [
            [
                'title' => 'Tech Conference 2026',
                'description' => 'Tech',
                'date' => now()->addDays(30),
                'location'=>'Budapest ELTE A épület',
                'max_attendees'=> 100,
            ],
            [
                'title' => 'Marketing workshop',
                'description' => 'Innovatív konferencia',
                'date' => now()->addDays(15),
                'location'=>'Online (zoom)',
                'max_attendees'=>50
            ],
            [
                'title' => 'Webfejlesztés alapjai',
                'description' => 'Meeting',
                'date' => now()->subDays(10), //múltban volt
                'location'=>'Debreceni egyetem',
                'max_attendees'=> 40
            ],
        ];

        foreach ($events as $event) {
            Event::create($event);
        }

        //random
        Event::factory()->count(10)->create();
        $this->command->info('EventSeeder: 13 esemény létrehozva (3 fix + 10 random)');
    }
}
