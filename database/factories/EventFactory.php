<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Event>
 */
class EventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
         $eventTypes = [
            'Tech konferencia',
            'Marketing workshop',
            'Üzleti találkozó',
            'Művészeti kiállítás',
            'Tudományos előadás',
            'Közösségi esemény',
            'Sportverseny',
            'Zenei fesztivál',
            'Képzési nap',
            'Hálózatépítő esemény'
        ];

        $topics = [
            'Innovatív technológiák',
            'Digitális marketing trendek',
            'Vállalkozásfejlesztés',
            'Művészet és kreativitás',
            'Tudományos kutatások',
            'Közösségi fejlődés',
            'Fenntarthatóság',
            'Személyes fejlődés'
        ]; 

        $locations = [
            'Budapest, BME Q épület',
            'Budapest, Corvinus Egyetem',
            'Online (Zoom)',
            'Szeged, SZTE',
            'Debrecen, Egyetem',
            'Pécs, PTE',
            'Győr, Széchenyi Egyetem',
            'Miskolc, Műszaki Egyetem',
            'Veszprém, Pannon Egyetem',
            'Budapest, Magvető Café'
        ];

        return [
            'title' => $this->faker->randomElement($eventTypes),
            'description' => $this->faker->randomElement($topics),
            'location' => $this->faker->randomElement($locations),
            'date' => $this->faker->dateTimeBetween('-1 month', '+6 months'),
            'max_attendees' => $this->faker->numberBetween(10,200),
        ];
    }
}
