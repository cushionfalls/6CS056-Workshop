<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkshopThreeTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_all_students_returns_collection(): void
    {
        Student::create([
            'name' => 'Alice Test',
            'email' => 'alice@example.com',
            'phone' => '1234567890',
            'address' => 'Kathmandu',
            'date_of_birth' => '2000-01-15',
        ]);

        Student::create([
            'name' => 'Bob Test',
            'email' => 'bob@example.com',
            'phone' => '0987654321',
            'address' => 'Lalitpur',
            'date_of_birth' => '1998-05-20',
        ]);

        $response = $this->getJson('/api/students');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'email',
                        'phone',
                        'address',
                        'date_of_birth',
                        'age',
                    ],
                ],
            ])
            ->assertJsonCount(2, 'data');
    }

    public function test_store_student_success(): void
    {
        $payload = [
            'name' => 'Charlie Student',
            'email' => 'charlie.api@example.com',
            'phone' => '9841234567',
            'address' => 'Bhaktapur',
            'date_of_birth' => '2002-08-10',
        ];

        $response = $this->postJson('/api/students', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'data' => [
                    'name' => 'Charlie Student',
                    'email' => 'charlie.api@example.com',
                    'phone' => '9841234567',
                    'address' => 'Bhaktapur',
                    'date_of_birth' => '2002-08-10',
                ],
            ]);

        $this->assertDatabaseHas('students', [
            'email' => 'charlie.api@example.com',
        ]);
    }

    public function test_store_student_validation_failure(): void
    {
        $response = $this->postJson('/api/students', [
            'name' => '',
            'email' => 'invalid-email',
            'phone' => '',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email', 'phone']);
    }

    public function test_show_student_success(): void
    {
        $student = Student::create([
            'name' => 'Dave Test',
            'email' => 'dave@example.com',
            'phone' => '5551234567',
            'address' => 'Pokhara',
            'date_of_birth' => '2001-03-25',
        ]);

        $response = $this->getJson("/api/students/{$student->id}");

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $student->id,
                    'name' => 'Dave Test',
                    'email' => 'dave@example.com',
                    'phone' => '5551234567',
                    'address' => 'Pokhara',
                    'date_of_birth' => '2001-03-25',
                ],
            ]);
    }

    public function test_show_student_not_found(): void
    {
        $response = $this->getJson('/api/students/9999');
        $response->assertStatus(404);
    }

    public function test_update_student_success(): void
    {
        $student = Student::create([
            'name' => 'Eve Test',
            'email' => 'eve@example.com',
            'phone' => '1122334455',
            'address' => 'Chitwan',
            'date_of_birth' => '1999-12-12',
        ]);

        $updateData = [
            'name' => 'Eve Updated',
            'email' => 'eve@example.com', // same email should pass unique ignore
            'phone' => '9988776655',
            'address' => 'Kathmandu',
            'date_of_birth' => '1999-12-12',
        ];

        $response = $this->putJson("/api/students/{$student->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $student->id,
                    'name' => 'Eve Updated',
                    'phone' => '9988776655',
                    'address' => 'Kathmandu',
                ],
            ]);

        $this->assertDatabaseHas('students', [
            'id' => $student->id,
            'name' => 'Eve Updated',
            'phone' => '9988776655',
        ]);
    }

    public function test_update_student_duplicate_email_fails(): void
    {
        Student::create([
            'name' => 'First Student',
            'email' => 'first@example.com',
            'phone' => '1111111111',
        ]);

        $second = Student::create([
            'name' => 'Second Student',
            'email' => 'second@example.com',
            'phone' => '2222222222',
        ]);

        $response = $this->putJson("/api/students/{$second->id}", [
            'name' => 'Second Student Updated',
            'email' => 'first@example.com',
            'phone' => '2222222222',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_destroy_student_success(): void
    {
        $student = Student::create([
            'name' => 'Frank Delete',
            'email' => 'frank@example.com',
            'phone' => '3333333333',
        ]);

        $response = $this->deleteJson("/api/students/{$student->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('students', ['id' => $student->id]);
    }

    public function test_get_all_courses_returns_collection(): void
    {
        Course::create([
            'name' => 'Web Development',
            'description' => 'Full stack web development',
            'duration' => 12,
            'fee' => 500.00,
            'difficulty' => 'Medium',
            'is_active' => true,
        ]);

        Course::create([
            'name' => 'Mobile App Development',
            'description' => 'Flutter & Android',
            'duration' => 8,
            'fee' => 450.00,
            'difficulty' => 'Hard',
            'is_active' => true,
        ]);

        $response = $this->getJson('/api/courses');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'description',
                        'duration',
                        'fee',
                        'difficulty',
                        'is_active',
                    ],
                ],
            ])
            ->assertJsonCount(2, 'data');
    }

    public function test_store_course_success(): void
    {
        $payload = [
            'name' => 'Data Science',
            'description' => 'Python for data analysis and ML',
            'duration' => 16,
            'fee' => 600.00,
            'difficulty' => 'Hard',
            'is_active' => true,
        ];

        $response = $this->postJson('/api/courses', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'data' => [
                    'name' => 'Data Science',
                    'duration' => 16,
                    'fee' => '600.00',
                    'difficulty' => 'Hard',
                    'is_active' => true,
                ],
            ]);

        $this->assertDatabaseHas('courses', [
            'name' => 'Data Science',
        ]);
    }

    public function test_store_course_validation_failure(): void
    {
        $response = $this->postJson('/api/courses', [
            'name' => '',
            'duration' => -1,
            'fee' => 'invalid',
            'difficulty' => 'SuperHard',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'duration', 'fee', 'difficulty']);
    }

    public function test_show_course_success(): void
    {
        $course = Course::create([
            'name' => 'UI/UX Design',
            'description' => 'Figma and wireframing',
            'duration' => 6,
            'fee' => 300.00,
            'difficulty' => 'Easy',
            'is_active' => true,
        ]);

        $response = $this->getJson("/api/courses/{$course->id}");

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $course->id,
                    'name' => 'UI/UX Design',
                    'difficulty' => 'Easy',
                ],
            ]);
    }

    public function test_show_course_not_found(): void
    {
        $response = $this->getJson('/api/courses/9999');
        $response->assertStatus(404);
    }

    public function test_update_course_success(): void
    {
        $course = Course::create([
            'name' => 'DevOps Intro',
            'description' => 'Docker basics',
            'duration' => 4,
            'fee' => 250.00,
            'difficulty' => 'Medium',
            'is_active' => true,
        ]);

        $updateData = [
            'name' => 'DevOps Advanced',
            'description' => 'Docker and Kubernetes',
            'duration' => 8,
            'fee' => 400.00,
            'difficulty' => 'Hard',
            'is_active' => false,
        ];

        $response = $this->putJson("/api/courses/{$course->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $course->id,
                    'name' => 'DevOps Advanced',
                    'duration' => 8,
                    'is_active' => false,
                ],
            ]);

        $this->assertDatabaseHas('courses', [
            'id' => $course->id,
            'name' => 'DevOps Advanced',
            'is_active' => false,
        ]);
    }

    public function test_destroy_course_success(): void
    {
        $course = Course::create([
            'name' => 'Course To Delete',
            'duration' => 2,
            'fee' => 100.00,
            'difficulty' => 'Easy',
            'is_active' => true,
        ]);

        $response = $this->deleteJson("/api/courses/{$course->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('courses', ['id' => $course->id]);
    }
}
