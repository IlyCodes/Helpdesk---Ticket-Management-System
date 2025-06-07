<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\TicketStatus;
use Illuminate\Support\Str;

class TicketStatusSeeder extends Seeder
{
    public function run(): void
    {
        $statuses = [
            ['name' => 'Open', 'color_class' => 'bg-blue-100 text-blue-800'],
            ['name' => 'Pending', 'color_class' => 'bg-yellow-100 text-yellow-800'],
            ['name' => 'In Progress', 'color_class' => 'bg-indigo-100 text-indigo-800'],
            ['name' => 'Resolved', 'color_class' => 'bg-green-100 text-green-800'],
            ['name' => 'Closed', 'color_class' => 'bg-gray-100 text-gray-800'],
            ['name' => 'Not Assigned', 'color_class' => 'bg-red-100 text-red-800'],
        ];

        foreach ($statuses as $status) {
            TicketStatus::firstOrCreate(
                ['slug' => Str::slug($status['name'])],
                ['name' => $status['name'], 'color_class' => $status['color_class']]
            );
        }
    }
}
