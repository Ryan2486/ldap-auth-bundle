<?php

declare(strict_types=1);

namespace LdapAuthBundle\Security;

use Symfony\Component\Security\Core\User\UserInterface;

final readonly class LdapUser implements UserInterface
{
    /**
     * @param list<string> $roles
     */
    public function __construct(
        private string $identifier,
        private array $roles = ['ROLE_USER'],
    ) {
    }

    public function getUserIdentifier(): string
    {
        return $this->identifier;
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    public function eraseCredentials(): void
    {
    }
}
