<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\Waiter;
use Illuminate\Database\Seeder;

class EventSeeder extends Seeder
{
    public function run(): void
    {
        $faker = \Faker\Factory::create();

        $locations = [
            ['city' => 'Naga City', 'venues' => ['Camsur Sports Complex', 'Village Hotel Convention Hall', 'Naga City Civic Center', 'Avenue Plaza Hotel', 'Naga Metropolitan Cathedral Hall', 'Crown Hotel Ballroom', 'Peñafrancia Resort', 'Naga Wonderland Park', 'Camarines Sur Provincial Capitol', 'Brgy. Concepcion Covered Court']],
            ['city' => 'Iriga City', 'venues' => ['Iriga City Convention Center', 'Mount Isarog View Resort', 'Iriga City Gymnasium', 'Brgy. San Antonio Hall', 'La Roca Veranda', 'Iriga City Plaza', 'Brgy. San Juan Covered Court', 'Mount Iriga Hotel', 'San Antonio de Padua Church Hall', 'Iriga Public Market Event Area']],
            ['city' => 'Pili', 'venues' => ['Pili Municipal Gym', 'Camsur Watersports Complex', 'Brgy. Cadlan Hall', 'Pili Capitol Convention Center', 'Brgy. San Jose Covered Court', 'Pili Town Plaza', 'Brgy. Lupi Hall', 'Pili Municipal Park', 'Brgy. Curry Covered Court', 'Pili Public Market']],
            ['city' => 'Nabua', 'venues' => ['Nabua Municipal Gym', 'Brgy. La Purisima Hall', 'Nabua National High School Gym', 'Brgy. San Esteban Hall', 'Nabua Town Plaza', 'Brgy. Sangay Covered Court', 'Nabua Catholic Church Hall', 'Brgy. Dolor Hall', 'Nabua Public Market', 'Brgy. Paloyon Hall']],
            ['city' => 'Baao', 'venues' => ['Baao Municipal Gym', 'Brgy. San Jose Hall', 'Baao Town Plaza', 'Brgy. Sagrada Hall', 'Baao Catholic Church Hall', 'Brgy. San Juan Covered Court', 'Baao Public Market', 'Brgy. Caranday Hall', 'Baao Community Center', 'Brgy. Del Pilar Hall']],
            ['city' => 'Bula', 'venues' => ['Bula Municipal Gym', 'Brgy. Sagrada Hall', 'Bula Town Plaza', 'Brgy. San Ramon Hall', 'Bula Catholic Church Hall', 'Brgy. San Francisco Covered Court', 'Bula Public Market', 'Brgy. Oma-Oma Hall', 'Bula Community Center', 'Brgy. Salvacion Hall']],
            ['city' => 'Calabanga', 'venues' => ['Calabanga Municipal Gym', 'Brgy. San Lucas Hall', 'Calabanga Town Plaza', 'Brgy. San Bernardino Hall', 'Calabanga Catholic Church Hall', 'Brgy. San Pablo Covered Court', 'Calabanga Public Market', 'Brgy. San Miguel Hall', 'Calabanga Community Center', 'Brgy. Santa Cruz Hall']],
            ['city' => 'Libmanan', 'venues' => ['Libmanan Municipal Gym', 'Brgy. Bahay Hall', 'Libmanan Town Plaza', 'Brgy. Cobo Hall', 'Libmanan Catholic Church Hall', 'Brgy. Poblacion Covered Court', 'Libmanan Public Market', 'Brgy. Malibago Hall', 'Libmanan Community Center', 'Brgy. San Isidro Hall']],
            ['city' => 'Sipocot', 'venues' => ['Sipocot Municipal Gym', 'Brgy. Tara Hall', 'Sipocot Town Plaza', 'Brgy. Calagbangan Hall', 'Sipocot Catholic Church Hall', 'Brgy. North Centro Covered Court', 'Sipocot Public Market', 'Brgy. Impig Hall', 'Sipocot Community Center', 'Brgy. South Centro Hall']],
            ['city' => 'Milaor', 'venues' => ['Milaor Municipal Gym', 'Brgy. San Antonio Hall', 'Milaor Town Plaza', 'Brgy. San Jose Hall', 'Milaor Catholic Church Hall', 'Brgy. San Roque Covered Court', 'Milaor Public Market', 'Brgy. San Vicente Hall', 'Milaor Community Center', 'Brgy. San Isidro Hall']],
            ['city' => 'Camaligan', 'venues' => ['Camaligan Municipal Gym', 'Brgy. San Jose Hall', 'Camaligan Town Plaza', 'Brgy. Sua Hall', 'Camaligan Catholic Church Hall', 'Brgy. Dugcalon Covered Court', 'Camaligan Public Market', 'Brgy. San Mateo Hall', 'Camaligan Community Center', 'Brgy. San Roque Hall']],
            ['city' => 'Canaman', 'venues' => ['Canaman Municipal Gym', 'Brgy. San Jose Hall', 'Canaman Town Plaza', 'Brgy. San Felipe Hall', 'Canaman Catholic Church Hall', 'Brgy. San Juan Covered Court', 'Canaman Public Market', 'Brgy. San Pablo Hall', 'Canaman Community Center', 'Brgy. San Isidro Hall']],
            ['city' => 'Magarao', 'venues' => ['Magarao Municipal Gym', 'Brgy. San Juan Hall', 'Magarao Town Plaza', 'Brgy. San Isidro Hall', 'Magarao Catholic Church Hall', 'Brgy. San Miguel Covered Court', 'Magarao Public Market', 'Brgy. San Antonio Hall', 'Magarao Community Center', 'Brgy. San Vicente Hall']],
            ['city' => 'Bombon', 'venues' => ['Bombon Municipal Gym', 'Brgy. San Antonio Hall', 'Bombon Town Plaza', 'Brgy. San Isidro Hall', 'Bombon Catholic Church Hall', 'Brgy. San Vicente Covered Court', 'Bombon Public Market', 'Brgy. San Jose Hall', 'Bombon Community Center', 'Brgy. San Roque Hall']],
            ['city' => 'Pamplona', 'venues' => ['Pamplona Municipal Gym', 'Brgy. San Gabriel Hall', 'Pamplona Town Plaza', 'Brgy. San Vicente Hall', 'Pamplona Catholic Church Hall', 'Brgy. San Antonio Covered Court', 'Pamplona Public Market', 'Brgy. San Isidro Hall', 'Pamplona Community Center', 'Brgy. San Jose Hall']],
        ];

        $eventNames = [
            'Wedding Reception', 'Birthday Party', 'Debut Celebration', 'Corporate Dinner',
            'Barangay Fiesta', 'Christening/Baptism', 'Graduation Party', 'Anniversary Dinner',
            'Funeral Reception', 'Company Team Building', 'Product Launch', 'Christmas Party',
            'New Year Celebration', 'Valentines Dinner', 'Mother\'s Day Feast', 'Father\'s Day BBQ',
            'Reunion Party', 'Seminar Catering', 'Conference Lunch', 'Political Rally',
            'School Foundation Day', 'Church Fundraiser', 'Sports Banquet', 'Award Night',
            'Engagement Party', 'Bridal Shower', 'Baby Shower', 'Retirement Party',
            'House Blessing', 'Store Opening', 'Town Fiesta', 'Beach Wedding',
            'Garden Party', 'Cocktail Reception', 'Buffet Dinner', 'Sit-down Dinner',
            'Kiddie Party', 'Senior Citizen Gala', 'Medical Convention', 'Lawyers\' Dinner',
            'Teachers\' Day', 'Christmas Eve Dinner', 'Wedding Anniversary', 'Despedida de Soltera',
            'Bachelor Party', 'Bachelorette Party', 'Victory Party', 'Election Celebration',
            'Barangay Assembly', 'Cooperative Meeting', 'Bank Event', 'Hotel Function',
            'Resort Event', 'Private Dinner', 'Charity Dinner', 'Auction Night',
            'Music Festival Catering', 'Art Exhibit Reception', 'Book Launch', 'Fashion Show',
            'Pageant After-party', 'Motorcycle Club Gathering', 'Car Club Meet', 'Alumni Homecoming',
            'Batch Reunion', 'Family Reunion', 'Clan Gathering', 'Indigenous Festival',
            'Harvest Festival', 'Flores de Mayo', 'Penafrancia Devotion Dinner', 'Lenten Gathering',
            'Easter Celebration', 'All Saints Day Reception', 'Undas Gathering', 'Christmas Caroling Party',
            'Media Dinner', 'Press Conference Lunch', 'Government Event', 'NGO Gathering',
            'Coastal Cleanup Lunch', 'Tree Planting Lunch', 'Medical Mission Dinner', 'Dental Mission Lunch',
            'Vaccination Drive Lunch', 'Disaster Relief Kitchen', 'Feeding Program', 'Scholarship Dinner',
            'Sponsorship Dinner', 'Partnership Meeting Lunch', 'Contract Signing Dinner', 'Board Meeting Lunch',
            'Stockholders Dinner', 'Investor Meeting', 'Startup Pitch Dinner', 'Tech Conference Lunch',
            'Real Estate Launch', 'Restaurant Opening', 'Cafe Soft Opening', 'Bakery Launch',
            'Farm Wedding', 'Church Wedding Reception', 'Civil Wedding Lunch', 'Destination Wedding Dinner',
            'Intimate Dinner', 'Grand Ball', 'Masquerade Party', 'Halloween Party',
            'Thanksgiving Dinner', 'Mid-Autumn Festival', 'Moon Festival Dinner', 'Chinese New Year Feast',
            'Korean BBQ Night', 'Japanese Dinner', 'Italian Feast', 'Filipino Fiesta',
            'Seafood Buffet', 'Lechon Party', 'Grill Night', 'Hot Pot Gathering',
            'Noodle Festival', 'Dessert Party', 'Wine Tasting Dinner', 'Coffee Appreciation Lunch',
            'Tea Ceremony', 'Cocktail Mixing Party', 'Cooking Class Lunch', 'Chef\'s Table Dinner',
            'Pop-up Dinner', 'Underground Dinner', 'Rooftop Party', 'Pool Party',
            'Yacht Party', 'Cruise Dinner', 'Train Journey Lunch', 'Road Trip Stop',
            'Camping Dinner', 'Glamping Feast', 'Picnic Lunch', 'Park Gathering',
            'Plaza Event', 'Street Festival', 'Night Market Food', 'Food Truck Rally',
            'Cook-off Competition', 'Baking Contest', 'Cake Decorating Party', 'Cupcake Workshop',
            'Chocolate Tasting', 'Cheese Board Night', 'Charcuterie Party', 'Sushi Rolling Class',
            'Pizza Making Party', 'Pasta Workshop', 'Dim Sum Brunch', 'Taco Tuesday Party',
            'Burger Bash', 'Fried Chicken Feast', 'Sinigang Night', 'Adobo Festival',
            'Laing Party', 'Bicol Express Feast', 'Kinalas Dinner', 'Pili Nut Tasting',
            'Gulay Festival', 'Isda Festival', 'Manok Festival', 'Baboy Festival',
            'Tinapa Night', 'Daing Breakfast', 'Champorado Brunch', 'Puto Bumbong Party',
            'Bibingka Morning', 'Suman Merienda', 'Kakanin Festival', 'Native Delicacies Fair',
            'Organic Dinner', 'Vegan Night', 'Vegetarian Feast', 'Pescatarian Dinner',
            'Gluten-Free Lunch', 'Keto Dinner', 'Paleo Feast', 'Low-Sodium Meal',
            'Diabetic-Friendly Dinner', 'Heart-Healthy Lunch', 'Athlete Meal Prep', 'Bodybuilder Feast',
            'Post-Workout Dinner', 'Pre-Game Lunch', 'Recovery Meal', 'Celebration Dinner',
            'Memorial Service Lunch', 'Wake Reception', 'Nine-Day Novena Meal', 'Forty-Day Mass Lunch',
            'First Death Anniversary', 'Death Anniversary Dinner', 'Birthday Mass Lunch', 'Thanksgiving Mass Dinner',
            'Confirmation Reception', 'First Communion Party', 'Wedding Renewal', 'Vow Renewal Dinner',
            'Adoption Celebration', 'Welcome Home Party', 'Bon Voyage Dinner', 'Send-off Lunch',
            'Homecoming Feast', 'Arrival Dinner', 'Farewell Party', 'Goodbye Lunch',
            'Retirement Gala', 'Promotion Dinner', 'New Job Celebration', 'Transfer Party',
            'Moving Out Dinner', 'Moving In Lunch', 'Housewarming', 'Apartment Warming',
            'Condo Blessing', 'Office Blessing', 'Store Blessing', 'Warehouse Blessing',
            'Farm Blessing', 'Fishing Boat Blessing', 'Vehicle Blessing Lunch', 'Equipment Blessing',
        ];

        $waiters = Waiter::where('status', 'active')->get();
        $adminId = \App\Models\User::where('role', 'admin')->first()->id ?? 1;

        $bar = $this->command->getOutput()->createProgressBar(200);
        $bar->start();

        for ($i = 0; $i < 200; $i++) {
            $location = $faker->randomElement($locations);
            $venue = $faker->randomElement($location['venues']);
            $fullAddress = $venue . ', ' . $location['city'] . ', Camarines Sur';

            $rand = rand(1, 10);
            if ($rand <= 3) {
                $eventDate = $faker->dateTimeBetween('-12 months', '-1 day');
                $status = $faker->randomElement(['completed', 'cancelled']);
            } elseif ($rand <= 8) {
                $eventDate = $faker->dateTimeBetween('+1 day', '+6 months');
                $status = 'scheduled';
            } else {
                $eventDate = $faker->dateTimeBetween('-3 days', '+3 days');
                $status = $faker->randomElement(['scheduled', 'ongoing']);
            }

            $setupTime = (clone $eventDate)->modify('-3 hours');

            $event = Event::create([
                'event_name' => $faker->randomElement($eventNames) . ' - ' . $location['city'],
                'venue_address' => $fullAddress,
                'event_date' => $eventDate,
                'setup_time' => $setupTime,
                'guest_count' => $faker->numberBetween(20, 500),
                'status' => $status,
                'notes' => $faker->optional(0.3)->sentence(10),
                'created_by' => $adminId,
            ]);

            $assignedWaiters = $waiters->random(rand(1, min(4, $waiters->count())));
            foreach ($assignedWaiters as $waiter) {
                $event->waiters()->attach($waiter->id, [
                    'role' => $faker->randomElement(['head_waiter', 'waiter', 'setup_crew']),
                    'confirmation_status' => $status === 'completed' ? 'confirmed' : $faker->randomElement(['pending', 'confirmed', 'declined']),
                    'confirmed_at' => $faker->optional(0.7)->dateTimeBetween('-1 month', 'now'),
                ]);
            }

            $bar->advance();
        }

        $bar->finish();
        $this->command->newLine();
        $this->command->info('Created 200 events in Camarines Sur with assigned waiters.');
    }
}