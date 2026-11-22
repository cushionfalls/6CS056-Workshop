@props(['course' => null])
@include('course._form', ['course' => $course ?? new \App\Models\Course(['is_active' => true])])
