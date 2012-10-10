<?php

namespace Symfony\Cmf\Bundle\CreateBundle\Document;

use Doctrine\ODM\PHPCR\Mapping\Annotations as PHPCRODM;
use Doctrine\Common\Collections\ArrayCollection;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Cmf\Bundle\CreateBundle\Model\ImageInterface;

/**
 * @PHPCRODM\Document(referenceable=true)
 */
class Image implements ImageInterface
{
    /**
     * @PHPCRODM\Id
     */
    protected $id;

    /**
     * @Assert\NotBlank
     * @Assert\Regex("{^[a-z]+$}")
     * @PHPCRODM\String()
     */
    protected $caption;

    /**
     * @PHPCRODM\String()
     */
    protected $mimeType;

    /**
     * @Assert\NotBlank()
     * @PHPCRODM\Binary()
     */
    protected $content;

    /**
     * @PHPCRODM\String(multivalue=true)
     */
    protected $tags;

    public function setId($path)
    {
        $this->id = $path;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getCaption()
    {
        return $this->caption;
    }

    public function getContent()
    {
        return $this->content;
    }

    public function getMimeType()
    {
        return $this->mimeType;
    }

    public function getTags()
    {
        return $this->tags;
    }

    public function setCaption($caption)
    {
        $this->caption = $caption;
    }

    public function setContent($content)
    {
        $this->content = $content;
    }

    public function setMimeType($mimeType)
    {
        $this->mimeType = $mimeType;
    }

    public function setTags(array $tags)
    {
        $this->tags = $tags;
    }

    public function __toString()
    {
        return $this->caption;
    }
}
