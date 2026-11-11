<?php

declare(strict_types=1);

namespace LdapAuthBundle\Tests\Service;

use LdapAuthBundle\Service\DevLdapProvider;
use LdapAuthBundle\Service\LdapProviderFactory;
use LdapAuthBundle\Service\SymfonyLdapProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Ldap\LdapInterface;

final class LdapProviderFactoryTest extends TestCase
{
    public function testCreatesDevProviderWhenMockEnabled(): void
    {
        $factory = new LdapProviderFactory(
            ldap: $this->createStub(LdapInterface::class),
            mock: true,
            baseDn: '@bcm.int',
        );

        $this->assertInstanceOf(DevLdapProvider::class, $factory->create());
    }

    public function testCreatesSymfonyProviderWhenMockDisabled(): void
    {
        $factory = new LdapProviderFactory(
            ldap: $this->createStub(LdapInterface::class),
            mock: false,
            baseDn: '@bcm.int',
        );

        $this->assertInstanceOf(SymfonyLdapProvider::class, $factory->create());
    }
}
