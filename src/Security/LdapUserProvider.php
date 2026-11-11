<?php

declare(strict_types=1);

namespace LdapAuthBundle\Security;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * @implements UserProviderInterface<UserInterface>
 */
final readonly class LdapUserProvider implements UserProviderInterface
{
    public function __construct(
        private LdapUserResolverInterface $resolver,
    ) {
    }

    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        return $this->resolver->resolve($identifier);
    }

    public function refreshUser(UserInterface $user): UserInterface
    {
        return $this->resolver->resolve($user->getUserIdentifier());
    }

    public function supportsClass(string $class): bool
    {
        return is_a($class, UserInterface::class, true);
    }
}
