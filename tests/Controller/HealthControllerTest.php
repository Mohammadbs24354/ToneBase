<?php

namespace App\Tests\Controller;

use App\Controller\HealthController;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Result;
use PHPUnit\Framework\TestCase;

final class HealthControllerTest extends TestCase
{
    public function testHealthEndpointReturnsServiceStatus(): void
    {
        $connection = $this->createMock(Connection::class);
        $response = (new HealthController($connection))();
        $payload = json_decode($response->getContent(), true);

        self::assertSame(200, $response->getStatusCode());
        self::assertSame('ok', $payload['status']);
        self::assertSame('ToneBase API', $payload['service']);
        self::assertSame('1.0.0', $payload['version']);
    }

    public function testReadinessEndpointChecksDatabaseConnection(): void
    {
        $result = $this->createMock(Result::class);
        $result->method('fetchOne')->willReturn(1);
        $connection = $this->createMock(Connection::class);
        $connection->expects(self::once())->method('executeQuery')->with('SELECT 1')->willReturn($result);

        $response = (new HealthController($connection))->readiness();
        $payload = json_decode($response->getContent(), true);

        self::assertSame(200, $response->getStatusCode());
        self::assertSame('ready', $payload['status']);
        self::assertSame('connected', $payload['database']);
    }
}
