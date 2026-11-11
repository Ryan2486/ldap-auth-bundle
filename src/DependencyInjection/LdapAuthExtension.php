<?php

declare(strict_types=1);

namespace LdapAuthBundle\DependencyInjection;

use LdapAuthBundle\Security\DefaultLdapUserResolver;
use LdapAuthBundle\Security\LdapUserResolverInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

final class LdapAuthExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $config = $this->processConfiguration(new Configuration(), $configs);

        $container->setParameter('ldap_auth.connection.dsn', $config['connection']['dsn']);
        $container->setParameter('ldap_auth.connection.encryption', $config['connection']['encryption']);
        $container->setParameter('ldap_auth.connection.protocol_version', $config['connection']['protocol_version']);
        $container->setParameter('ldap_auth.connection.referrals', $config['connection']['referrals']);
        $container->setParameter('ldap_auth.connection.network_timeout', $config['connection']['network_timeout']);
        $container->setParameter('ldap_auth.base_dn', $config['base_dn']);
        $container->setParameter('ldap_auth.mock', $config['mock']);
        $container->setParameter('ldap_auth.login_path', $config['login_path']);
        $container->setParameter('ldap_auth.logout_path', $config['logout_path']);
        $container->setParameter('ldap_auth.default_target_path', $config['default_target_path']);
        $container->setParameter('ldap_auth.layout_template', $config['layout_template']);
        $container->setParameter('ldap_auth.login_template', $config['login_template']);

        $loader = new YamlFileLoader($container, new FileLocator(\dirname(__DIR__, 2).'/config'));
        $loader->load('services.yaml');

        $container->setAlias(
            LdapUserResolverInterface::class,
            $config['user_resolver'] ?? DefaultLdapUserResolver::class,
        );
    }
}
