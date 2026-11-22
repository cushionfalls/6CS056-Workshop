<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkshopOneTest extends TestCase
{
    use RefreshDatabase;

    public function test_students_list_page_loads(): void
    {
        $response = $this->get('/students');
        $response->assertStatus(200);
        $response->assertSee('Students');
    }

    public function test_create_student_page_loads(): void
    {
        $response = $this->get('/students/create');
        $response->assertStatus(200);
        $response->assertSee('Create Student');
    }

    public function test_store_student_success(): void
    {
        $studentData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '1234567890',
            'address' => '123 Main St',
            'date_of_birth' => '2000-01-15',
        ];

        $response = $this->post('/students', $studentData);

        $response->assertRedirect('/students');
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('students', [
            'email' => 'john@example.com',
            'name' => 'John Doe',
        ]);
    }

    public function test_store_student_validation_failure(): void
    {
        $response = $this->post('/students', [
            'name' => '',
            'email' => 'not-an-email',
            'phone' => '',
        ]);

        $response->assertSessionHasErrors(['name', 'email', 'phone']);
    }

    public function test_show_student(): void
    {
        $student = Student::create([
            'name' => 'Alice Smith',
            'email' => 'alice@example.com',
            'phone' => '9876543210',
            'address' => '456 Elm St',
            'date_of_birth' => '1999-05-20',
        ]);

        $response = $this->get('/students/' . $student->id);
        $response->assertStatus(200);
        $response->assertSee('Alice Smith');
        $response->assertSee('alice@example.com');
    }

    public function test_update_student(): void
    {
        $student = Student::create([
            'name' => 'Bob Builder',
            'email' => 'bob@example.com',
            'phone' => '5551234567',
            'address' => '789 Oak Ave',
            'date_of_birth' => '1995-10-10',
        ]);

        $response = $this->put('/students/' . $student->id, [
            'name' => 'Bob The Builder',
            'email' => 'bob@example.com',
            'phone' => '5559876543',
            'address' => 'New Address',
            'date_of_birth' => '1995-10-10',
        ]);

        $response->assertRedirect('/students/' . $student->id);
        $this->assertDatabaseHas('students', [
            'id' => $student->id,
            'name' => 'Bob The Builder',
            'phone' => '5559876543',
        ]);
    }

    public function test_delete_student(): void
    {
        $student = Student::create([
            'name' => 'Delete Me',
            'email' => 'delete@example.com',
            'phone' => '0000000000',
        ]);

        $response = $this->delete('/students/' . $student->id);
        $response->assertRedirect('/students');
        $this->assertDatabaseMissing('students', [
            'id' => $student->id,
        ]);
    }

    public function test_courses_list_page_loads(): void
    {
        $response = $this->get('/courses');
        $response->assertStatus(200);
        $response->assertSee('Courses');
    }

    public function test_create_course_page_loads(): void
    {
        $response = $this->get('/courses/create');
        $response->assertStatus(200);
        $response->assertSee('Create Course');
    }

    public function test_store_course_success(): void
    {
        $courseData = [
            'name' => 'Web Development',
            'description' => 'Full stack web development course',
            'duration' => 12,
            'fee' => 499.99,
            'difficulty' => 'Medium',
            'is_active' => '1',
        ];

        $response = $this->post('/courses', $courseData);
        $response->assertRedirect('/courses');
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('courses', [
            'name' => 'Web Development',
            'difficulty' => 'Medium',
        ]);
    }

    public function test_show_course(): void
    {
        $course = Course::create([
            'name' => 'Database Design',
            'description' => 'SQL and database architecture',
            'duration' => 8,
            'fee' => 299.50,
            'difficulty' => 'Hard',
            'is_active' => true,
        ]);

        $response = $this->get('/courses/' . $course->id);
        $response->assertStatus(200);
        $response->assertSee('Database Design');
        $response->assertSee('Hard');
    }

    public function test_update_course(): void
    {
        $course = Course::create([
            'name' => 'Python Basics',
            'description' => 'Intro to Python',
            'duration' => 6,
            'fee' => 199.00,
            'difficulty' => 'Easy',
            'is_active' => true,
        ]);

        $response = $this->put('/courses/' . $course->id, [
            'name' => 'Python Advanced',
            'description' => 'Advanced Python and AI',
            'duration' => 10,
            'fee' => 399.00,
            'difficulty' => 'Hard',
            'is_active' => '1',
        ]);

        $response->assertRedirect('/courses/' . $course->id);
        $this->assertDatabaseHas('courses', [
            'id' => $course->id,
            'name' => 'Python Advanced',
            'difficulty' => 'Hard',
        ]);
    }

    public function test_delete_course(): void
    {
        $course = Course::create([
            'name' => 'Archived Course',
            'duration' => 4,
            'fee' => 100,
            'difficulty' => 'Easy',
            'is_active' => false,
        ]);

        $response = $this->delete('/courses/' . $course->id);
        $response->assertRedirect('/courses');
        $this->assertDatabaseMissing('courses', [
            'id' => $course->id,
        ]);
    }
}
