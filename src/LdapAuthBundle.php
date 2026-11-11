<?php

declare(strict_types=1);

namespace LdapAuthBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

final class LdapAuthBundle extends Bundle
{
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }
}
