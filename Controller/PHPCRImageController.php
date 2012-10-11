<?php

namespace Symfony\Cmf\Bundle\CreateBundle\Controller;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\FileBag;

use PHPCR\Query\QueryInterface;

class PHPCRImageController extends ImageController
{
    protected $staticPath;

    public function setStaticPath($staticPath)
    {
        $this->staticPath = $staticPath;
    }

    protected function generateId($name)
    {
        return $this->staticPath.'/'.$name;
    }

    protected function getNameFromId($id)
    {
        $path = explode('/', $id);
        return array_pop($path);
    }

    protected function generateName(UploadedFile $file)
    {
        return $file->getClientOriginalName();
    }

    protected function getImagesByName($name, $offset, $limit)
    {
        $sql = 'SELECT * FROM [nt:unstructured]
                    WHERE ISDESCENDANTNODE([nt:unstructured], ' . $this->manager->quote($this->staticPath) . ')
                        AND [nt:unstructured].[phpcr:class] = ' . $this->manager->quote($this->imageClass) . '
                        AND [nt:unstructured].name LIKE ' . $this->manager->quote($name.'%');

        $query = $this->manager->createQuery($sql, QueryInterface::JCR_SQL2);
        $query->setLimit($offset);
        $query->setLimit($limit);
        return $this->manager->getDocumentsByQuery($query);
    }

    protected function getImagesByTag(array $tags, $offset, $limit)
    {
        $sql = 'SELECT * FROM [nt:unstructured]
                    WHERE ISDESCENDANTNODE([nt:unstructured], ' . $this->manager->quote($this->staticPath) . ')
                        AND [nt:unstructured].[phpcr:class] = ' . $this->manager->quote($this->imageClass);

        if (!empty($tags)) {
            foreach ($tags as $i => $tag) {
                $tags[$i] = 'tags = ' . $this->manager->quote($tag);
            }

            $sql.= ' AND (' . implode(' OR ', $tags) . ')';
        }

        $query = $this->manager->createQuery($sql, QueryInterface::JCR_SQL2);
        $query->setLimit($offset);
        $query->setLimit($limit);
        return $this->manager->getDocumentsByQuery($query);
    }
}
