<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CertificateResource;
use App\Services\CertificateService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CertificateController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected CertificateService $certificateService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $certificates = $this->certificateService->getUserCertificates($request->user());
        return $this->success(__('messages.certificates_retrieved'), CertificateResource::collection($certificates));
    }

    public function show(Request $request, string $id): JsonResponse
    {
        $certificate = $this->certificateService->find($request->user(), $id);
        return $this->success(__('messages.certificate_retrieved'), new CertificateResource($certificate));
    }

    public function verify(string $certificateNumber): JsonResponse
    {
        $certificate = $this->certificateService->verify($certificateNumber);

        if (! $certificate) {
            return $this->notFound(__('messages.certificate_invalid'));
        }
        return $this->success(__('messages.certificate_verified'), new CertificateResource($certificate));
    }
}