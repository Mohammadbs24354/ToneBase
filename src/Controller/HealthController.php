<?php

namespace App\Controller;

use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class HealthController
{
    public function __construct(private readonly Connection $connection) {}

    #[Route('/api/v1/health', name: 'api_health', methods: ['GET'])]
    public function __invoke(): JsonResponse
    {
        return new JsonResponse([
            'status' => 'ok',
            'service' => 'ToneBase API',
            'version' => '1.0.0',
        ]);
    }

    #[Route('/api/v1/health/ready', name: 'api_health_ready', methods: ['GET'])]
    public function readiness(): JsonResponse
    {
        try {
            $this->connection->executeQuery('SELECT 1')->fetchOne();
            return new JsonResponse(['status' => 'ready', 'database' => 'connected']);
        } catch (\Throwable) {
            return new JsonResponse(['status' => 'not_ready', 'database' => 'unavailable'], 503);
        }
    }
}
