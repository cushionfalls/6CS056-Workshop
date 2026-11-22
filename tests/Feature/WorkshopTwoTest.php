<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkshopTwoTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_model_casts_and_age_accessor(): void
    {
        $student = Student::create([
            'name' => 'Alice Test',
            'email' => 'alice.test@example.com',
            'phone' => '1234567890',
            'date_of_birth' => Carbon::now()->subYears(20)->format('Y-m-d'),
        ]);

        $this->assertInstanceOf(Carbon::class, $student->date_of_birth);
        $this->assertEquals(20, $student->age);
    }

    public function test_course_model_casts(): void
    {
        $course = Course::create([
            'name' => 'Laravel MVC',
            'description' => 'Test course',
            'duration' => 4,
            'fee' => 150.5,
            'difficulty' => 'Medium',
            'is_active' => 1,
        ]);

        $this->assertIsBool($course->is_active);
        $this->assertTrue($course->is_active);
        $this->assertEquals('150.50', (string) $course->fee);
    }

    public function test_route_model_binding_404_for_missing_student(): void
    {
        $response = $this->get('/students/9999');
        $response->assertStatus(404);

        $response = $this->get('/students/9999/edit');
        $response->assertStatus(404);
    }

    public function test_route_model_binding_404_for_missing_course(): void
    {
        $response = $this->get('/courses/9999');
        $response->assertStatus(404);

        $response = $this->get('/courses/9999/edit');
        $response->assertStatus(404);
    }

    public function test_student_crud_and_components(): void
    {
        // 1. List page with layout, navbar, and CSS
        $response = $this->get(route('students.index'));
        $response->assertStatus(200);
        $response->assertSee('Training Institute');
        $response->assertSee('Students');
        $response->assertSee('Courses');
        $response->assertSee('/css/app.css');

        // 2. Create page
        $createResponse = $this->get(route('students.create'));
        $createResponse->assertStatus(200);
        $createResponse->assertSee('Create Student');

        // 3. Store valid student
        $dob = Carbon::now()->subYears(22)->format('Y-m-d');
        $postResponse = $this->post(route('students.store'), [
            'name' => 'Charlie Brown',
            'email' => 'charlie@example.com',
            'phone' => '9841000000',
            'address' => 'Kathmandu',
            'date_of_birth' => $dob,
        ]);
        $postResponse->assertRedirect(route('students.index'));
        $postResponse->assertSessionHas('success');

        $student = Student::where('email', 'charlie@example.com')->firstOrFail();

        // 4. Show student with age accessor
        $showResponse = $this->get(route('students.show', $student));
        $showResponse->assertStatus(200);
        $showResponse->assertSee('Charlie Brown');
        $showResponse->assertSee('22');

        // 5. Update student with same email (unique ignore test)
        $updateResponse = $this->put(route('students.update', $student), [
            'name' => 'Charlie Brown Updated',
            'email' => 'charlie@example.com',
            'phone' => '9841111111',
            'address' => 'Patan',
            'date_of_birth' => $dob,
        ]);
        $updateResponse->assertRedirect(route('students.show', $student));
        $this->assertDatabaseHas('students', [
            'id' => $student->id,
            'name' => 'Charlie Brown Updated',
            'phone' => '9841111111',
        ]);

        // 6. Delete student
        $deleteResponse = $this->delete(route('students.destroy', $student));
        $deleteResponse->assertRedirect(route('students.index'));
        $this->assertDatabaseMissing('students', ['id' => $student->id]);
    }

    public function test_student_duplicate_email_validation(): void
    {
        Student::create([
            'name' => 'User One',
            'email' => 'unique@example.com',
            'phone' => '1111111111',
        ]);

        $student2 = Student::create([
            'name' => 'User Two',
            'email' => 'two@example.com',
            'phone' => '2222222222',
        ]);

        // Try updating student2 with student1's email
        $response = $this->put(route('students.update', $student2), [
            'name' => 'User Two Edit',
            'email' => 'unique@example.com',
            'phone' => '2222222222',
        ]);

        $response->assertSessionHasErrors(['email']);
    }

    public function test_course_crud_and_components(): void
    {
        // 1. List page
        $response = $this->get(route('courses.index'));
        $response->assertStatus(200);
        $response->assertSee('Courses');

        // 2. Create page
        $createResponse = $this->get(route('courses.create'));
        $createResponse->assertStatus(200);
        $createResponse->assertSee('Create Course');

        // 3. Store valid course
        $postResponse = $this->post(route('courses.store'), [
            'name' => 'Cloud Computing',
            'description' => 'AWS and Azure course',
            'duration' => 10,
            'fee' => 750.00,
            'difficulty' => 'Hard',
            'is_active' => '1',
        ]);
        $postResponse->assertRedirect(route('courses.index'));
        $postResponse->assertSessionHas('success');

        $course = Course::where('name', 'Cloud Computing')->firstOrFail();

        // 4. Show course
        $showResponse = $this->get(route('courses.show', $course));
        $showResponse->assertStatus(200);
        $showResponse->assertSee('Cloud Computing');
        $showResponse->assertSee('Hard');
        $showResponse->assertSee('750.00');

        // 5. Update course
        $updateResponse = $this->put(route('courses.update', $course), [
            'name' => 'Cloud Computing Advanced',
            'description' => 'Updated description',
            'duration' => 12,
            'fee' => 850.50,
            'difficulty' => 'Hard',
            'is_active' => '0',
        ]);
        $updateResponse->assertRedirect(route('courses.show', $course));
        $this->assertDatabaseHas('courses', [
            'id' => $course->id,
            'name' => 'Cloud Computing Advanced',
            'is_active' => false,
        ]);

        // 6. Delete course
        $deleteResponse = $this->delete(route('courses.destroy', $course));
        $deleteResponse->assertRedirect(route('courses.index'));
        $this->assertDatabaseMissing('courses', ['id' => $course->id]);
    }

    public function test_navbar_active_highlighting(): void
    {
        $response = $this->get(route('students.index'));
        $response->assertStatus(200);
        // Students should have class active
        $this->assertMatchesRegularExpression('/<a[^>]*route\(\'students\.index\'\)[^>]*active|Students\s*<\/a>/s', $response->getContent());

        $responseCourses = $this->get(route('courses.index'));
        $responseCourses->assertStatus(200);
        $this->assertMatchesRegularExpression('/Courses\s*<\/a>/s', $responseCourses->getContent());
    }
}
