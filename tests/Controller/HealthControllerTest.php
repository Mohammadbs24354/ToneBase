<?php

namespace App\Tests\Controller;

use App\Controller\HealthController;
use PHPUnit\Framework\TestCase;

final class HealthControllerTest extends TestCase
{
    public function testHealthEndpointReturnsServiceStatus(): void
    {
        $response = (new HealthController())();
        $payload = json_decode($response->getContent(), true);

        self::assertSame(200, $response->getStatusCode());
        self::assertSame('ok', $payload['status']);
        self::assertSame('ToneBase API', $payload['service']);
        self::assertSame('1.0.0', $payload['version']);
    }
}
