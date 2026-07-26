<?php

use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CertificateController;
use App\Http\Controllers\Api\CourseController;
use App\Http\Controllers\Api\EnrollmentController;
use App\Http\Controllers\Api\LessonController;
use App\Http\Controllers\Api\ProgressController;
use App\Http\Controllers\Api\QuestionController;
use App\Http\Controllers\Api\QuizAttemptController;
use App\Http\Controllers\Api\QuizController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\SectionController;
use App\Http\Controllers\Api\UserProfileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'active'])->get('/user', function (Request $request) {
    return $request->user();
});

// User Profile
Route::middleware(['auth:sanctum', 'active'])->group(function () {
    Route::get('/profile', [UserProfileController::class, 'show']);
    Route::post('/profile', [UserProfileController::class, 'update']);
    Route::put('/profile/password', [UserProfileController::class, 'changePassword']);
    Route::delete('/profile', [UserProfileController::class, 'destroy']);
});

// Categories
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('categories/{category}', [CategoryController::class, 'show']);

Route::middleware(['auth:sanctum', 'active', 'role:admin'])->group(function () {
    Route::apiResource('categories', CategoryController::class)->except(['index', 'show']);
});

// Courses
Route::get('/courses', [CourseController::class, 'index']);
Route::get('/courses/{course:slug}', [CourseController::class, 'show']);

Route::middleware(['auth:sanctum', 'active'])->group(function () {
    Route::get('/my-courses', [CourseController::class, 'myCourses']);
    Route::post('/courses', [CourseController::class, 'store']);
    Route::post('/courses/{course}', [CourseController::class, 'update']);
    Route::patch('/courses/{course}/toggle-publish', [CourseController::class, 'togglePublish']);
    Route::delete('/courses/{course}', [CourseController::class, 'destroy']);
});

// Sections
Route::middleware(['auth:sanctum', 'active'])->prefix('courses/{course}/sections')->scopeBindings()->group(function () {
    Route::get('/', [SectionController::class, 'index']);
    Route::post('/', [SectionController::class, 'store']);
    Route::patch('/{section}', [SectionController::class, 'update']);
    Route::delete('/{section}', [SectionController::class, 'destroy']);
    Route::post('/reorder', [SectionController::class, 'reorder']);
});

// Lessons
Route::middleware(['auth:sanctum', 'active'])->prefix('courses/{course}/sections/{section}/lessons')->scopeBindings()->group(function () {
    Route::get('/', [LessonController::class, 'index']);
    Route::post('/', [LessonController::class, 'store']);
    Route::patch('/{lesson}', [LessonController::class, 'update']);
    Route::delete('/{lesson}', [LessonController::class, 'destroy']);
    Route::post('/reorder', [LessonController::class, 'reorder']);
});

// Enrollment
Route::middleware(['auth:sanctum', 'active'])->group(function () {
    Route::get('/enrollments', [EnrollmentController::class, 'index']);
    Route::post('/enrollments', [EnrollmentController::class, 'store'])->middleware(['verified', 'throttle:10,1']);
    Route::get('/enrollments/{id}', [EnrollmentController::class, 'show']);
    Route::delete('/enrollments/{enrollment}', [EnrollmentController::class, 'destroy']);
    Route::get('/courses/{course}/enrollments', [EnrollmentController::class, 'courseEnrollments']);
});

// Progress
Route::middleware(['auth:sanctum', 'active'])->group(function () {
    Route::post('/lessons/{lesson}/progress', [ProgressController::class, 'update']);
    Route::get('/courses/{course}/progress', [ProgressController::class, 'courseProgress']);
});

// Quiz
Route::middleware(['auth:sanctum', 'active'])->group(function () {
    Route::get('/sections/{section}/quiz', [QuizController::class, 'show']);
    Route::post('/sections/{section}/quiz', [QuizController::class, 'store']);
    Route::put('/quizzes/{quiz}', [QuizController::class, 'update']);
    Route::delete('/quizzes/{quiz}', [QuizController::class, 'destroy']);
});

// Quiz Attempt
Route::middleware(['auth:sanctum', 'active'])->group(function () {
    Route::post('/quizzes/{quiz}/attempts', [QuizAttemptController::class, 'start'])->middleware('throttle:10,1');
    Route::get('/quizzes/{quiz}/attempts', [QuizAttemptController::class, 'index']);
    Route::post('/attempts/{attempt}/submit', [QuizAttemptController::class, 'submit'])->middleware('throttle:10,1');
    Route::get('/attempts/{attempt}', [QuizAttemptController::class, 'show']);
});

// Question
Route::middleware(['auth:sanctum', 'active'])->prefix('quizzes/{quiz}/questions')->scopeBindings()->group(function () {
    Route::get('/', [QuestionController::class, 'index']);
    Route::post('/', [QuestionController::class, 'store']);
    Route::put('/{question}', [QuestionController::class, 'update']);
    Route::delete('/{question}', [QuestionController::class, 'destroy']);
});

// Reviews
Route::get('/courses/{course}/reviews', [ReviewController::class, 'index']);
Route::get('/courses/{course}/reviews/summary', [ReviewController::class, 'summary']);

Route::middleware(['auth:sanctum', 'active'])->group(function () {
    Route::post('/courses/{course}/reviews', [ReviewController::class, 'store'])->middleware('throttle:10,1');
    Route::put('/reviews/{review}', [ReviewController::class, 'update']);
    Route::delete('/reviews/{review}', [ReviewController::class, 'destroy']);
});

// Certificates
Route::get('/certificates/verify/{certificateNumber}', [CertificateController::class, 'verify'])->middleware('throttle:15,1');

Route::middleware(['auth:sanctum', 'active'])->group(function () {
    Route::get('/certificates', [CertificateController::class, 'index']);
    Route::get('/certificates/{id}', [CertificateController::class, 'show']);
});

require __DIR__ . '/auth.php';
