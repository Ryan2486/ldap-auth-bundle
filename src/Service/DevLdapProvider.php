<?php

declare(strict_types=1);

namespace LdapAuthBundle\Service;

final class DevLdapProvider implements LdapProviderInterface
{
    public function checkCredentials(string $identifier, string $password): bool
    {
        return '' !== $identifier && '' !== $password && 'wrong' !== $password;
    }
}
