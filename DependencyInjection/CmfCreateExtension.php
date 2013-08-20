<?php

namespace Symfony\Cmf\Bundle\CreateBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

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

        // load config
        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

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

        if ($config['rest_controller_class']) {
            $container->setParameter($this->getAlias().'.rest.controller.class', $config['rest_controller_class']);
        }

        if (!empty($config['persistence']['phpcr']['enabled'])) {
            $this->loadPhpcr($config['persistence']['phpcr'], $loader, $container);
        } else {
            $container->setParameter($this->getAlias() . '.image_enabled', false);
        }
    }

    public function loadPhpcr($config, XmlFileLoader $loader, ContainerBuilder $container)
    {
        $container->setParameter($this->getAlias() . '.backend_type_phpcr', true);

        $container->setParameter($this->getAlias().'.persistence.phpcr.manager_name', $config['manager_name']);

        $loader->load('persistence-phpcr.xml');

        if (isset($config['image']['enabled'])) {
            $loader->load('controller-image-phpcr.xml');

            $container->setParameter($this->getAlias() . '.image_enabled', true);
            $container->setParameter($this->getAlias() . '.persistence.phpcr.image.class', $config['image']['model_class']);
            $container->setParameter($this->getAlias() . '.persistence.phpcr.image_controller.class', $config['image']['controller_class']);
            $container->setParameter($this->getAlias() . '.persistence.phpcr.image_basepath', $config['image']['basepath']);
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
