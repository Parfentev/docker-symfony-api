<?php

namespace Controller\V1;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/v1.1')]
class AuthController extends AbstractController
{
    #[Route('/users/auth', methods: 'POST')]
    public function auth(): JsonResponse
    {
        return $this->json([]);
    }

    #[Route('/users/refresh', methods: 'POST')]
    public function refresh(): JsonResponse
    {
        return $this->json([]);
    }

    #[Route('/users/logout', methods: 'POST')]
    public function logout(): JsonResponse
    {
        return $this->json([]);
    }
}
