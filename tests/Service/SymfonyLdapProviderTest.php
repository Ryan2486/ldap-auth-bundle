<?php

declare(strict_types=1);

namespace LdapAuthBundle\Tests\Service;

use LdapAuthBundle\Service\Exception\LdapException;
use LdapAuthBundle\Service\SymfonyLdapProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Ldap\Exception\ConnectionException;
use Symfony\Component\Ldap\Exception\InvalidCredentialsException;
use Symfony\Component\Ldap\LdapInterface;

final class SymfonyLdapProviderTest extends TestCase
{
    public function testReturnsTrueOnSuccessfulBind(): void
    {
        $ldap = $this->createMock(LdapInterface::class);
        $ldap->expects($this->once())
            ->method('bind')
            ->with('jdoe@bcm.int', 'secret');

        $provider = new SymfonyLdapProvider($ldap, '@bcm.int');

        $this->assertTrue($provider->checkCredentials('jdoe', 'secret'));
    }

    public function testReturnsFalseOnInvalidCredentials(): void
    {
        $ldap = $this->createMock(LdapInterface::class);
        $ldap->method('bind')->willThrowException(new InvalidCredentialsException('Invalid credentials.'));

        $provider = new SymfonyLdapProvider($ldap, '@bcm.int');

        $this->assertFalse($provider->checkCredentials('jdoe', 'wrong'));
    }

    public function testThrowsLdapExceptionOnConnectionFailure(): void
    {
        $ldap = $this->createMock(LdapInterface::class);
        $ldap->method('bind')->willThrowException(new ConnectionException('Connection failed.'));

        $provider = new SymfonyLdapProvider($ldap, '@bcm.int');

        $this->expectException(LdapException::class);
        $provider->checkCredentials('jdoe', 'secret');
    }
}
