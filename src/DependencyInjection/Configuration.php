<?php

declare(strict_types=1);

namespace LdapAuthBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('ldap_auth');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->arrayNode('connection')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('dsn')->isRequired()->cannotBeEmpty()->end()
                        ->enumNode('encryption')->values(['none', 'tls', 'ssl'])->defaultValue('none')->end()
                        ->integerNode('protocol_version')->defaultValue(3)->end()
                        ->booleanNode('referrals')->defaultFalse()->end()
                        ->integerNode('network_timeout')->defaultValue(5)->end()
                    ->end()
                ->end()
                ->scalarNode('base_dn')->isRequired()->cannotBeEmpty()->end()
                ->booleanNode('mock')->defaultFalse()->end()
                ->scalarNode('login_path')->defaultValue('/login')->end()
                ->scalarNode('logout_path')->defaultValue('/logout')->end()
                ->scalarNode('default_target_path')->defaultValue('/')->end()
                ->scalarNode('layout_template')->defaultValue('@LdapAuth/layout.html.twig')->end()
                ->scalarNode('login_template')->defaultValue('@LdapAuth/security/login.html.twig')->end()
                ->scalarNode('user_resolver')->defaultNull()->end()
            ->end();

        return $treeBuilder;
    }
}
