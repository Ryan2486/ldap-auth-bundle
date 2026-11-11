<?php

declare(strict_types=1);

namespace LdapAuthBundle\Service;

interface LdapProviderInterface
{
    public function checkCredentials(string $identifier, string $password): bool;
}
