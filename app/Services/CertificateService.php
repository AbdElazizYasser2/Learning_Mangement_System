<?php

namespace App\Services;

use App\Models\Certificate;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CertificateService
{
    public function issueForEnrollment(Enrollment $enrollment): ?Certificate
    {
        if (! $enrollment->isCompleted()) {
            return null;
        }

        $existing = Certificate::query()
            ->where('user_id', $enrollment->user_id)
            ->where('course_id', $enrollment->course_id)
            ->first();

        if ($existing) {
            return $existing;
        }

        return DB::transaction(function () use ($enrollment) {
            return Certificate::create([
                'user_id'             => $enrollment->user_id,
                'course_id'           => $enrollment->course_id,
                'enrollment_id'       => $enrollment->id,
                'certificate_number'  => $this->generateCertificateNumber(),
                'issued_at'           => now(),
            ]);
        });
    }

    public function getUserCertificates(User $user)
    {
        return Certificate::query()
            ->with('course.instructor')
            ->where('user_id', $user->id)
            ->latest('issued_at')
            ->get();
    }

    public function find(User $user, string $id): Certificate
    {
        return Certificate::query()
            ->with(['user', 'course.instructor'])
            ->where('user_id', $user->id)
            ->findOrFail($id);
    }

    public function verify(string $certificateNumber): ?Certificate
    {
        return Certificate::query()
            ->with(['user', 'course'])
            ->where('certificate_number', $certificateNumber)
            ->first();
    }

    protected function generateCertificateNumber(): string
    {
        do {
            $number = 'CERT-' . now()->format('Y') . '-' . strtoupper(Str::random(8));
        } while (Certificate::where('certificate_number', $number)->exists());

        return $number;
    }
}