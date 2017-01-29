<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2017 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\CreateBundle\DependencyInjection;

use Midgard\CreatePHP\RestService;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class CmfCreateExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $processor = new Processor();
        $configuration = new Configuration();
        $config = $processor->processConfiguration($configuration, $configs);

        // load config
        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

        $container->setParameter('cmf_create.map', $config['map']);

        $container->setParameter('cmf_create.stanbol_url', $config['stanbol_url']);

        $container->setParameter('cmf_create.fixed_toolbar', $config['fixed_toolbar']);

        $container->setParameter('cmf_create.editor_base_path', $config['editor_base_path']);

        if (empty($config['plain_text_types'])) {
            $config['plain_text_types'] = array('dcterms:title', 'schema:headline');
        }
        $container->setParameter('cmf_create.plain_text_types', $config['plain_text_types']);

        if ($config['auto_mapping']) {
            foreach ($container->getParameter('kernel.bundles') as $bundleShortName => $class) {
                $bundle = new \ReflectionClass($class);

                $rdfMappingDir = $container->getParameter('kernel.root_dir').'/Resources/'.$bundleShortName.'/rdf-mappings';
                if (file_exists($rdfMappingDir)) {
                    $config['rdf_config_dirs'][] = $rdfMappingDir;
                }

                $rdfMappingDir = dirname($bundle->getFilename()).'/Resources/rdf-mappings';
                if (file_exists($rdfMappingDir)) {
                    $config['rdf_config_dirs'][] = $rdfMappingDir;
                }
            }
        }

        $container->setParameter('cmf_create.rdf_config_dirs', $config['rdf_config_dirs']);

        if ($config['rest_force_request_locale']) {
            $bundles = $container->getParameter('kernel.bundles');
            if (!isset($bundles['CmfCoreBundle'])) {
                throw new InvalidConfigurationException('You need to enable "CmfCoreBundle" when activating the "rest_force_request_locale" option');
            }
        }
        $container->setParameter('cmf_create.rest.force_request_locale', $config['rest_force_request_locale']);

        $this->loadSecurity($config['security'], $loader, $container);

        if ($this->isConfigEnabled($container, $config['persistence']['phpcr'])) {
            $this->loadPhpcr($config['persistence']['phpcr'], $loader, $container);
        } else {
            // TODO: we should leverage the mediabundle here and not depend on phpcr
            $container->setParameter('cmf_create.image_enabled', false);
        }
        if ($this->isConfigEnabled($container, $config['persistence']['orm'])) {
            $this->loadOrm($config['persistence']['orm'], $loader, $container);
        }
        $container->setAlias('cmf_create.object_mapper', $config['object_mapper_service_id']);
    }

    protected function loadSecurity($config, XmlFileLoader $loader, ContainerBuilder $container)
    {
        $container->setParameter('cmf_create.security.role', $config['role']);
        if (isset($config['checker_service'])) {
            $service = $config['checker_service'];
        } elseif (false === $config['role']) {
            $service = 'cmf_create.security.always_allow_checker';
        } else {
            $service = 'cmf_create.security.role_access_checker';
        }
        $container->setAlias('cmf_create.security.checker', $service);
    }

    public function loadPhpcr($config, XmlFileLoader $loader, ContainerBuilder $container)
    {
        $container->setParameter('cmf_create.persistence.phpcr.manager_name', $config['manager_name']);

        $loader->load('persistence-phpcr.xml');

        if ($config['image']['enabled']) {
            $loader->load('controller-image-phpcr.xml');

            $container->setParameter('cmf_create.image_enabled', true);
            $container->setParameter('cmf_create.persistence.phpcr.image.class', $config['image']['model_class']);
            $container->setParameter('cmf_create.persistence.phpcr.image_basepath', $config['image']['basepath']);
        } else {
            $container->setParameter('cmf_create.image_enabled', false);
        }

        if ($config['delete']) {
            $restHandler = $container->getDefinition('cmf_create.rest.handler');
            $restHandler->addMethodCall('setWorkflow', array(RestService::HTTP_DELETE, new Reference('cmf_create.persistence.phpcr.delete_workflow')));
        }
    }

    public function loadOrm($config, XmlFileLoader $loader, ContainerBuilder $container)
    {
        $loader->load('persistence-orm.xml');
        $container->setParameter('cmf_create.persistence.orm.manager_name', $config['manager_name']);
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
