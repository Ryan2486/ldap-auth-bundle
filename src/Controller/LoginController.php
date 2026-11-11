<?php

declare(strict_types=1);

namespace LdapAuthBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

final class LoginController extends AbstractController
{
    public function __construct(
        private readonly string $loginTemplate,
        private readonly string $layoutTemplate,
    ) {
    }

    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        return $this->render($this->loginTemplate, [
            'last_username' => $authenticationUtils->getLastUsername(),
            'error' => $authenticationUtils->getLastAuthenticationError(),
            'layout_template' => $this->layoutTemplate,
        ]);
    }

    public function logout(): never
    {
        throw new \LogicException('This route is intercepted by the logout firewall listener.');
    }
}
