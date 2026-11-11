<?php

declare(strict_types=1);

namespace LdapAuthBundle\Security;

use Symfony\Component\Security\Core\User\UserInterface;

interface LdapUserResolverInterface
{
    public function resolve(string $identifier): UserInterface;
}
