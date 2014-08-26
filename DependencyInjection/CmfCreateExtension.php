<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2014 Symfony CMF
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

        $container->setParameter($this->getAlias().'.fixed_toolbar', $config['fixed_toolbar']);

        $container->setParameter($this->getAlias().'.editor_base_path', $config['editor_base_path']);

        if (empty($config['plain_text_types'])) {
            $config['plain_text_types'] = array('dcterms:title', 'schema:headline');
        }
        $container->setParameter($this->getAlias().'.plain_text_types', $config['plain_text_types']);

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

        $container->setParameter($this->getAlias().'.rdf_config_dirs', $config['rdf_config_dirs']);

        if ($config['rest_controller_class']) {
            $container->setParameter($this->getAlias().'.rest.controller.class', $config['rest_controller_class']);
        }

        if ($config['rest_force_request_locale']) {
            $bundles = $container->getParameter('kernel.bundles');
            if (!isset($bundles['CmfCoreBundle'])) {
                throw new InvalidConfigurationException('You need to enable "CmfCoreBundle" when activating the "rest_force_request_locale" option');
            }
        }
        $container->setParameter($this->getAlias().'.rest.force_request_locale', $config['rest_force_request_locale']);

        $this->loadSecurity($config['security'], $loader, $container);

        $hasMapper = false;
        if ($config['persistence']['phpcr']['enabled']) {
            $this->loadPhpcr($config['persistence']['phpcr'], $loader, $container);
            $hasMapper = true;
        } else {
            // TODO: we should leverage the mediabundle here and not depend on phpcr
            $container->setParameter($this->getAlias() . '.image_enabled', false);
        }
        if (isset($config['object_mapper_service_id'])) {
            $container->setAlias($this->getAlias() . '.object_mapper', $config['object_mapper_service_id']);
            $hasMapper = true;
        }
        if (!$hasMapper) {
            throw new InvalidConfigurationException('You need to either enable one of the persistence layers or set the cmf_create.object_mapper_service_id option');
        }
    }

    protected function loadSecurity($config, XmlFileLoader $loader, ContainerBuilder $container)
    {
        $container->setParameter($this->getAlias().'.security.role', $config['role']);
        if (isset($config['checker_service'])) {
            $service = $config['checker_service'];
        } elseif (false === $config['role']) {
            $service = 'cmf_create.security.always_allow_checker';
        } else {
            $service = 'cmf_create.security.role_access_checker';
        }
        $container->setAlias($this->getAlias() . '.security.checker', $service);
    }

    public function loadPhpcr($config, XmlFileLoader $loader, ContainerBuilder $container)
    {
        $container->setParameter($this->getAlias() . '.backend_type_phpcr', true);
        $container->setAlias($this->getAlias() . '.object_mapper', $this->getAlias() . '.persistence.phpcr.object_mapper');

        $container->setParameter($this->getAlias().'.persistence.phpcr.manager_name', $config['manager_name']);

        $loader->load('persistence-phpcr.xml');

        if ($config['image']['enabled']) {
            $loader->load('controller-image-phpcr.xml');

            $container->setParameter($this->getAlias() . '.image_enabled', true);
            $container->setParameter($this->getAlias() . '.persistence.phpcr.image.class', $config['image']['model_class']);
            $container->setParameter($this->getAlias() . '.persistence.phpcr.image_controller.class', $config['image']['controller_class']);
            $container->setParameter($this->getAlias() . '.persistence.phpcr.image_basepath', $config['image']['basepath']);
        } else {
            $container->setParameter($this->getAlias() . '.image_enabled', false);
        }

        if ($config['delete']) {
            $restHandler = $container->getDefinition('cmf_create.rest.handler');
            $restHandler->addMethodCall('setWorkflow', array(RestService::HTTP_DELETE, new Reference('cmf_create.persistence.phpcr.delete_workflow')));
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
