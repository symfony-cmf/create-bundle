<?php

namespace Symfony\Cmf\Bundle\CreateBundle\Document;

interface ImageInterface
{
    public function getId();
    public function getName();
    public function getContent();
    public function getMimeType();
    public function getTags();
    public function setId($id);
    public function setName($name);
    public function setContent($content);
    public function setMimeType($mimeType);
    public function setTags(array $tags);
    public function __toString();
}
