<?php

namespace Database\Seeders;
use App\Models\Event;
use App\Models\User;
use App\Models\Registration;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RegistrationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        $events = Event::all();

        $sampleRegistrations = [
            [
                'user_id' => $users[1]->id,
                'event_id' => $events[0]->id,
                'status'=> 'elfogadva',
                'registered_at' => now()->subDay(5),
            ],
            [
                'user_id' => $users[1]->id,
                'event_id' => $events[1]->id,
                'status'=> 'függőben',
                'registered_at' => now()->subDay(3),
            ],
            [
                'user_id' => $users[2]->id,
                'event_id' => $events[1]->id,
                'status'=> 'elfogadva',
                'registered_at' => now()->subDay(7),
            ],
            [
                'user_id' => $users[3]->id,
                'event_id' => $events[2]->id,
                'status'=> 'elutasítva',
                'registered_at' => now()->subDay(10),
            ],
        ];

        foreach ($sampleRegistrations as $registration){
            Registration::create($registration);
        }

        //random
        foreach($users as $user){
            $randomEvents = $events->random(rand(1,3)); //minden userhez 1-3 random event
        
            foreach($randomEvents as $event){
                $exists = Registration::where('user_id', $user->id)
                    ->where('event_id', $event->id)
                    ->exists();

                if (!$exists){
                    Registration::create([
                        'user_id' => $user->id,
                        'event_id' => $event->id,
                        'status'=> collect(['függőben', 'elfogadva', 'elutasítva'])->random(),
                        'registered_at' => now()->subDay(rand(0,15)),
                    ]);
                }
            }

        }
        $this->command->info('RegistartionSeeder: 4 fix és ? véletlen jelentkezés létrehozva');
    }
}
