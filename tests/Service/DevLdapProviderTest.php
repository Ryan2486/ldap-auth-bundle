<?php

declare(strict_types=1);

namespace LdapAuthBundle\Tests\Service;

use LdapAuthBundle\Service\DevLdapProvider;
use PHPUnit\Framework\TestCase;

final class DevLdapProviderTest extends TestCase
{
    public function testAcceptsValidCredentials(): void
    {
        $provider = new DevLdapProvider();

        $this->assertTrue($provider->checkCredentials('jdoe', 'secret'));
    }

    public function testRejectsWrongPassword(): void
    {
        $provider = new DevLdapProvider();

        $this->assertFalse($provider->checkCredentials('jdoe', 'wrong'));
    }

    public function testRejectsEmptyValues(): void
    {
        $provider = new DevLdapProvider();

        $this->assertFalse($provider->checkCredentials('', 'secret'));
        $this->assertFalse($provider->checkCredentials('jdoe', ''));
    }
}
