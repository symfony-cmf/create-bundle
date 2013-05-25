<?php

namespace Symfony\Cmf\Bundle\CreateBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class CmfCreateExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $processor = new Processor();
        $configuration = new Configuration();
        $config = $processor->processConfiguration($configuration, $configs);

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        if (!empty($config['phpcr_odm'])) {
            $loader->load('phpcr_odm.xml');
            $documentManagerName = 'default';
            $managerRegistry = 'doctrine_phpcr';
            if (is_string($config['phpcr_odm'])) {
                $documentManagerName = $config['phpcr_odm'];
                $phpcr_odm = $container->getDefinition('cmf_create.object_mapper');
                $phpcr_odm->replaceArgument(2, $documentManagerName);
            }

            $container->setParameter($this->getAlias().'.manager_name', $documentManagerName);
        }

        $container->setParameter($this->getAlias().'.map', $config['map']);

        $container->setParameter($this->getAlias().'.stanbol_url', $config['stanbol_url']);

        $container->setParameter($this->getAlias().'.role', $config['role']);

        $container->setParameter($this->getAlias().'.fixed_toolbar', $config['fixed_toolbar']);

        $container->setParameter($this->getAlias().'.editor_base_path', $config['editor_base_path']);

        if (empty($config['plain_text_types'])) {
            $config['plain_text_types'][] = 'dcterms:title';
        }
        $container->setParameter($this->getAlias().'.plain_text_types', $config['plain_text_types']);

        if ($config['auto_mapping']) {
            foreach ($container->getParameter('kernel.bundles') as $class) {
                $bundle = new \ReflectionClass($class);
                $rdfMappingDir = dirname($bundle->getFilename()).'/Resources/rdf-mappings';
                if (file_exists($rdfMappingDir)) {
                    $config['rdf_config_dirs'][] = $rdfMappingDir;
                }
            }
        }

        $container->setParameter($this->getAlias().'.rdf_config_dirs', $config['rdf_config_dirs']);

        if (isset($config['image']) && isset($managerRegistry)) {
            $loader->load('image.xml');
            $definition = $container->getDefinition('cmf_create.image.controller');
            $definition->replaceArgument(0, new Reference($managerRegistry));
            $container->setParameter($this->getAlias().'.image.model_class', $config['image']['model_class']);
            $container->setParameter($this->getAlias().'.image.controller_class', $config['image']['controller_class']);

            if ('doctrine_phpcr' === $managerRegistry) {
                $definition->addMethodCall('setStaticPath', array($config['image']['static_basepath']));
            }
        } else {
            $container->setParameter($this->getAlias().'.image.model_class', false);
        }

        $loader->load('services.xml');

        if ($config['rest_controller_class']) {
            $container->setParameter($this->getAlias().'.rest.controller.class', $config['rest_controller_class']);
        }
    }

    /**
     * Returns the base path for the XSD files.
     *
     * @return string The XSD base path
     */
    public function getXsdValidationBasePath()
    {
        return __DIR__.'/../Resources/config/schema';
    }

    public function getNamespace()
    {
        return 'http://cmf.symfony.com/schema/dic/create';
    }
}
