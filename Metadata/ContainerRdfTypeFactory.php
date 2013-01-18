<?php

namespace Symfony\Cmf\Bundle\CreateBundle\Metadata;

use Midgard\CreatePHP\RdfMapperInterface;
use Midgard\CreatePHP\Type\TypeInterface;

use Midgard\CreatePHP\Metadata\RdfTypeFactory;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Midgard\CreatePHP\Metadata\RdfDriverInterface;

/**
 * TODO: comment ...
 * Use container to get instances of mappers instead of class names
 */
class ContainerRdfTypeFactory extends RdfTypeFactory implements ContainerAwareInterface
{
    /**
     * @var array service names of the mappers
     */
    private $mapperServices;

    /**
     * @var ContainerInterface
     *
     * @api
     */
    protected $container;

    /**
     * @param RdfMapperInterface $defaultMapper the default mapper to use if there is no specific one
     * @param RdfDriverInterface $driver the driver to load types from
     * @param array $mappers rdf mappers per service name
     */
    public function __construct(RdfMapperInterface $defaultMapper, RdfDriverInterface $driver, $mapperServices = array())
    {
        $this->mapperServices = $mapperServices;
        parent::__construct($defaultMapper, $driver);
    }

    /**
     * TODO: update the comment
     * Get the mapper for type $name, or the defaultMapper if there is no specific mapper.
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
     * @api
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }
}
