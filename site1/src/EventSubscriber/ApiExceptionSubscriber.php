<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

#[AsEventListener(event: KernelEvents::EXCEPTION, method: 'onApiException')]
class ApiExceptionSubscriber
{
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
}