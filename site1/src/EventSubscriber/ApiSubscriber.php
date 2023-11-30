<?php

namespace App\EventSubscriber;

use App\Entity\Auth\AccessEntity;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use SymfonyApiBase\Service\AuthService;

#[AsEventListener(event: KernelEvents::CONTROLLER, method: 'onController')]
class ApiSubscriber
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
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