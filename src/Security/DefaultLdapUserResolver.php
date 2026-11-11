<?php

declare(strict_types=1);

namespace LdapAuthBundle\Security;

use Symfony\Component\Security\Core\User\UserInterface;

final class DefaultLdapUserResolver implements LdapUserResolverInterface
{
    public function resolve(string $identifier): UserInterface
    {
        return new LdapUser($identifier);
    }
}
