<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Student;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Student::firstOrCreate(
            ['email' => 'john.doe@example.com'],
            [
                'name' => 'John Doe',
                'phone' => '9841234567',
                'address' => 'Kathmandu, Nepal',
                'date_of_birth' => '2001-05-15',
            ]
        );

        Student::firstOrCreate(
            ['email' => 'jane.smith@example.com'],
            [
                'name' => 'Jane Smith',
                'phone' => '9801234567',
                'address' => 'Pokhara, Nepal',
                'date_of_birth' => '2002-08-22',
            ]
        );

        Course::firstOrCreate(
            ['name' => 'Full-Stack Web Development'],
            [
                'description' => 'Comprehensive web development covering Laravel, MySQL, and modern frontend tools.',
                'duration' => 12,
                'fee' => 450.00,
                'difficulty' => 'Medium',
                'is_active' => true,
            ]
        );

        Course::firstOrCreate(
            ['name' => 'Database Architecture & SQL'],
            [
                'description' => 'In-depth guide to relational database design, query optimization, and normalization.',
                'duration' => 8,
                'fee' => 300.00,
                'difficulty' => 'Hard',
                'is_active' => true,
            ]
        );
    }
}
