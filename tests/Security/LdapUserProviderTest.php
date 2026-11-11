<?php

declare(strict_types=1);

namespace LdapAuthBundle\Tests\Security;

use LdapAuthBundle\Security\DefaultLdapUserResolver;
use LdapAuthBundle\Security\LdapUser;
use LdapAuthBundle\Security\LdapUserProvider;
use PHPUnit\Framework\TestCase;

final class LdapUserProviderTest extends TestCase
{
    public function testLoadsUserByIdentifier(): void
    {
        $provider = new LdapUserProvider(new DefaultLdapUserResolver());

        $user = $provider->loadUserByIdentifier('jdoe');

        $this->assertInstanceOf(LdapUser::class, $user);
        $this->assertSame('jdoe', $user->getUserIdentifier());
        $this->assertSame(['ROLE_USER'], $user->getRoles());
    }

    public function testRefreshesUser(): void
    {
        $provider = new LdapUserProvider(new DefaultLdapUserResolver());

        $user = $provider->refreshUser(new LdapUser('jdoe'));

        $this->assertSame('jdoe', $user->getUserIdentifier());
    }

    public function testSupportsUserImplementations(): void
    {
        $provider = new LdapUserProvider(new DefaultLdapUserResolver());

        $this->assertTrue($provider->supportsClass(LdapUser::class));
    }
}
