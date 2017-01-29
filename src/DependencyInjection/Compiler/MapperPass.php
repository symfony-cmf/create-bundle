<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2017 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\CreateBundle\DependencyInjection\Compiler;

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

/**
 * Adds tagged cmf_create.mapper services to the chain mapper.
 */
class MapperPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (false === $container->hasDefinition('cmf_create.chain_mapper')) {
            return;
        }

        $definition = $container->getDefinition('cmf_create.chain_mapper');

        $tags = $container->findTaggedServiceIds('cmf_create.mapper');
        if ($container->getAlias('cmf_create.object_mapper') == 'cmf_create.chain_mapper' && count($tags) == 0) {
            throw new InvalidConfigurationException('You need to either enable one of the persistence layers, set the cmf_create.object_mapper_service_id option, or tag a mapper with cmf_create.mapper');
        }

        foreach ($tags as $id => $tags) {
            foreach ($tags as $attributes) {
                $definition->addMethodCall('registerMapper', array(new Reference($id), $attributes['alias']));
            }
        }
    }
}
