<?php

namespace Symfony\Cmf\Bundle\CreateBundle\Mapper;

use Midgard\CreatePHP\Mapper\DoctrinePhpcrOdmMapper;

use Midgard\CreatePHP\Entity\PropertyInterface;

use Symfony\Cmf\Bundle\RoutingExtraBundle\Document\Route;

/**
 * Mapper to handle route documents stored in PHPCR-ODM.
 */
class RouteDoctrinePhpcrOdmMapper extends DoctrinePhpcrOdmMapper
{
    public function setPropertyValue($object, PropertyInterface $property, $value)
    {
        if ($object instanceof Route && $property->getIdentifier() === 'locale') {
            $object->setDefault('_locale', $value);
            $object->setRequirement('_locale', $value);
            return $object;
        }
        return parent::setPropertyValue($object, $property, $value);
    }

    public function getPropertyValue($object, PropertyInterface $property)
    {
        if ($object instanceof Route && $property->getIdentifier() === 'locale') {
            return $object->getDefault('_locale');
        }
        return parent::getPropertyValue($object, $property);
    }
}
