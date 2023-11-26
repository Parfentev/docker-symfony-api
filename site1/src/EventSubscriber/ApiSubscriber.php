<?php

namespace App\EventSubscriber;

use App\Entity\Auth\AccessEntity;
use App\Service\AuthService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

#[AsEventListener(event: KernelEvents::EXCEPTION, method: 'onApiException')]
#[AsEventListener(event: KernelEvents::CONTROLLER, method: 'onController')]
class ApiSubscriber
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function onApiException(ExceptionEvent $event): void
    {
        if (!str_starts_with($event->getRequest()->getPathInfo(), '/api/')) {
            return;
        }

        $exception = $event->getThrowable();

        $event->allowCustomResponseCode();
        $event->setResponse(new JsonResponse([
            'code'    => $exception->getCode(),
            'message' => $exception->getMessage(),
            'data'    => []
        ], $exception->statusCode ?? Response::HTTP_BAD_REQUEST));
    }

    public function onController(ControllerEvent $event): void
    {
        $request = $event->getRequest();
        if (!str_starts_with($request->getPathInfo(), '/api/')) {
            return;
        }

        $authorizationHeader = $request->headers->get('Authorization');
        if (!$authorizationHeader || !preg_match('/Bearer\s+(.+)/i', $authorizationHeader, $matches)) {
            return;
        }

        $entity = $this->entityManager->getRepository(AccessEntity::class)->find($matches[1]);
        if (!$entity || $entity->getExpiresAt() <= time()) {
            return;
        }

        AuthService::setCurrentUserId($entity->getUserId());
        AuthService::setToken($entity->getAccessToken());
    }
}