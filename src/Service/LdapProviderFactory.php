<?php

declare(strict_types=1);

namespace LdapAuthBundle\Service;

use Symfony\Component\Ldap\LdapInterface;

final readonly class LdapProviderFactory
{
    public function __construct(
        private LdapInterface $ldap,
        private bool $mock,
        private string $baseDn,
    ) {
    }

    public function create(): LdapProviderInterface
    {
        if ($this->mock) {
            return new DevLdapProvider();
        }

        return new SymfonyLdapProvider(
            ldap: $this->ldap,
            baseDn: $this->baseDn,
        );
    }
}
