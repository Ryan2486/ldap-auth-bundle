<?php

declare(strict_types=1);

namespace LdapAuthBundle\Security;

use LdapAuthBundle\Service\Exception\LdapException;
use LdapAuthBundle\Service\LdapProviderInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;
use Symfony\Component\Security\Http\SecurityRequestAttributes;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

final class LdapAuthenticator extends AbstractAuthenticator implements AuthenticationEntryPointInterface
{
    use TargetPathTrait;

    public const CSRF_TOKEN_ID = 'ldap_auth';

    public function __construct(
        private readonly LdapProviderInterface $ldapProvider,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly CsrfTokenManagerInterface $csrfTokenManager,
        private readonly string $loginRoute = 'ldap_auth_login',
        private readonly string $defaultTargetPath = '/',
    ) {
    }

    public function supports(Request $request): ?bool
    {
        if ($this->loginRoute !== $request->attributes->get('_route')) {
            return false;
        }

        return $request->isMethod('POST');
    }

    public function authenticate(Request $request): Passport
    {
        $identifier = trim((string) $request->request->get('_username', ''));
        $password = (string) $request->request->get('_password', '');
        $csrfToken = (string) $request->request->get('_csrf_token', '');

        $request->getSession()->set(SecurityRequestAttributes::LAST_USERNAME, $identifier);

            if (!$this->csrfTokenManager->isTokenValid(new CsrfToken(self::CSRF_TOKEN_ID, $csrfToken))) {
            throw new CustomUserMessageAuthenticationException('Jeton CSRF invalide.');
        }

        if ('' === $identifier || '' === $password) {
            throw new CustomUserMessageAuthenticationException('Identifiant et mot de passe requis.');
        }

        try {
            $valid = $this->ldapProvider->checkCredentials($identifier, $password);
        } catch (LdapException $exception) {
            throw new CustomUserMessageAuthenticationException('Service LDAP indisponible.', [], 0, $exception);
        }

        if (!$valid) {
            throw new CustomUserMessageAuthenticationException('Identifiants LDAP invalides.');
        }

        return new SelfValidatingPassport(new UserBadge($identifier));
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        $targetPath = $this->getTargetPath($request->getSession(), $firewallName) ?? $this->defaultTargetPath;

        return new RedirectResponse($targetPath);
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $request->getSession()->set(SecurityRequestAttributes::AUTHENTICATION_ERROR, $exception);

        return new RedirectResponse($this->urlGenerator->generate($this->loginRoute));
    }

    public function start(Request $request, ?AuthenticationException $authException = null): Response
    {
        return new RedirectResponse($this->urlGenerator->generate($this->loginRoute));
    }
}
