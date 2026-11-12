<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final class AdminWriteGuardSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::REQUEST => ['onKernelRequest', 20]];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        if (!str_starts_with($request->getPathInfo(), '/api/v1/admin/')) {
            return;
        }

        $configuredToken = (string) ($_ENV['API_ADMIN_TOKEN'] ?? $_SERVER['API_ADMIN_TOKEN'] ?? getenv('API_ADMIN_TOKEN') ?: '');
        if ($configuredToken === '') {
            $event->setResponse(new JsonResponse(['error' => 'Admin API is not configured.'], 503));
            return;
        }

        $providedToken = $request->headers->get('Authorization', '');
        if (!str_starts_with($providedToken, 'Bearer ') || !hash_equals($configuredToken, substr($providedToken, 7))) {
            $event->setResponse(new JsonResponse(['error' => 'Unauthorized.'], 401));
        }
    }
}
