<?php

namespace Symfony\Cmf\Bundle\CreateBundle\Document;

interface ImageInterface
{
    public function getId();
    public function getCaption();
    public function getContent();
    public function getMimeType();
    public function getTags();
    public function setId($id);
    public function setCaption($caption);
    public function setContent($content);
    public function setMimeType($mimeType);
    public function setTags(array $tags);
    public function __toString();
}
