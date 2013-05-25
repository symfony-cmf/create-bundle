<?php

namespace Symfony\Cmf\Bundle\CreateBundle\Serializer;

use JMS\Serializer\JsonSerializationVisitor;
use Symfony\Cmf\Bundle\CreateBundle\Document\Image;
use Symfony\Component\Routing\RouterInterface;

class Handler
{

    private $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    protected function getNameFromId($id)
    {
        $path = explode('/', $id);
        return array_pop($path);
    }

    /**
     * Handles the serialization of an Image object
     *
     * @param \JMS\Serializer\JsonSerializationVisitor $visitor
     * @param \Symfony\Cmf\Bundle\CreateBundle\Document\Image $image
     * @return array
     */
    public function serializeImageToJson(JsonSerializationVisitor $visitor, Image $image)
    {
        $url = $this->router->generate('cmf_create_image_display', array('name' => $this->getNameFromId($image->getId())), true);
        return array('id' => $image->getId(), 'url' => $url, 'alt' => $image->getCaption());
    }

}
