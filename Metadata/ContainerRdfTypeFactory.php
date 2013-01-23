<?php

namespace Symfony\Cmf\Bundle\CreateBundle\Metadata;

use Midgard\CreatePHP\RdfMapperInterface;
use Midgard\CreatePHP\Type\TypeInterface;

use Midgard\CreatePHP\Metadata\RdfTypeFactory;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Midgard\CreatePHP\Metadata\RdfDriverInterface;

/**
 * Factory for createphp types based on class names. If available, the mappers
 * for the requested class are loaded from the Symfony service container.
 */
class ContainerRdfTypeFactory extends RdfTypeFactory implements ContainerAwareInterface
{
    /**
     * @var array service names of the mappers per type
     */
    private $mapperServices;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @param RdfMapperInterface $defaultMapper the default mapper to use if there is no specific one
     * @param RdfDriverInterface $driver the driver to load types from
     * @param array $mapperServices rdf mappers service names per type
     */
    public function __construct(RdfMapperInterface $defaultMapper, RdfDriverInterface $driver, array $mapperServices = array())
    {
        $this->mapperServices = $mapperServices;
        parent::__construct($defaultMapper, $driver);
    }

    /**
     * Get the mapper for type $name in the $symfony container, or the defaultMapper if there is no specific one
     *
     * @param string $name the type name for which to get the mapper
     *
     * @return RdfMapperInterface
     */
    protected function getMapper($name)
    {
        if (isset($this->mapperServices[$name])) {
            return $this->container->get($this->mapperServices[$name]);
        }

        return parent::getMapper($name);
    }

    /**
     * @see ContainerAwareInterface::setContainer()
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }
}
