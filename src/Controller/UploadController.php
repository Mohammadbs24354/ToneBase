<?php

namespace App\Controller;

use App\Service\AudioStorageService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/v1/admin/uploads')]
final class UploadController
{
    public function __construct(private readonly AudioStorageService $storage) {}

    #[Route('', methods: ['POST'])]
    public function upload(Request $request): JsonResponse
    {
        $file = $request->files->get('file');
        if (!$file) return new JsonResponse(['error' => 'Send an audio file using the "file" form field.'], 422);

        try {
            return new JsonResponse(['data' => $this->storage->store($file)], 201);
        } catch (\InvalidArgumentException $exception) {
            return new JsonResponse(['error' => $exception->getMessage()], 415);
        } catch (\Throwable) {
            return new JsonResponse(['error' => 'Audio upload failed.'], 500);
        }
    }
}
