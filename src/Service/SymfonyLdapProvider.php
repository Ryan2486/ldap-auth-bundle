<?php

declare(strict_types=1);

namespace LdapAuthBundle\Service;

use LdapAuthBundle\Service\Exception\LdapException;
use Symfony\Component\Ldap\Exception\ConnectionException;
use Symfony\Component\Ldap\Exception\InvalidCredentialsException;
use Symfony\Component\Ldap\Exception\LdapException as SymfonyLdapException;
use Symfony\Component\Ldap\LdapInterface;

final readonly class SymfonyLdapProvider implements LdapProviderInterface
{
    public function __construct(
        private LdapInterface $ldap,
        private string $baseDn,
    ) {
    }

    public function checkCredentials(string $identifier, string $password): bool
    {
        try {
            $this->ldap->bind($identifier.$this->baseDn, $password);

            return true;
        } catch (InvalidCredentialsException) {
            return false;
        } catch (ConnectionException|SymfonyLdapException $exception) {
            throw new LdapException('LDAP authentication failed.', 0, $exception);
        }
    }
}
