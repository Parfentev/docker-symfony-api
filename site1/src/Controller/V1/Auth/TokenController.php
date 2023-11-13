<?php

namespace App\Auth;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use OAuth2\OAuth2ServerException;
use OAuth2\OAuth2;
use OAuth2\OAuth2ServerException as OAuth2Exception;

class TokenController extends AbstractController
{
    public function tokenAction(Request $request): Response
    {
        // Получаем данные из запроса
        $username = $request->request->get('username');
        $password = $request->request->get('password');

        // Проверяем наличие обязательных параметров
        if (!$username || !$password) {
            return new Response('Missing required parameters.', 400);
        }

        // Получаем сервис OAuth 2.0
        $server = $this->get('bshaffer_oauth2.server');

        try {
            // Проверяем учетные данные
            $user = $this->getDoctrine()->getRepository(User::class)->findOneBy(['username' => $username]);

            if (!$user || !password_verify($password, $user->getPassword())) {
                throw new BadCredentialsException('Invalid username or password');
            }

            // Генерируем токен доступа
            $response = $server->handleTokenRequest($request);

            return new Response(json_encode($response), 200, ['Content-Type' => 'application/json']);
        } catch (OAuth2Exception $e) {
            return new Response($e->getDescription(), $e->httpStatusCode, ['Content-Type' => 'application/json']);
        } catch (UsernameNotFoundException $e) {
            return new Response('Invalid username or password', 401, ['Content-Type' => 'application/json']);
        }
    }
}