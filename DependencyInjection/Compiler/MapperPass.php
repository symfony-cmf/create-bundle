<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2015 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\CreateBundle\DependencyInjection\Compiler;

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

        foreach ($container->findTaggedServiceIds('cmf_create.mapper') as $id => $attributes) {
            $definition->addMethodCall('registerMapper', array(new Reference($id), $id));
        }
    }
}
