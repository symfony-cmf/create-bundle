<?php

namespace Symfony\Cmf\Bundle\CreateBundle\Controller;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\FileBag;

use PHPCR\Query\QueryInterface;
use Symfony\Component\HttpFoundation\Request;

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

    protected function generateName(Request $request, UploadedFile $file)
    {
        return strlen($request->get('caption')) ? $request->get('caption') : $file->getClientOriginalName();
    }

    protected function getImagesByCaption($caption, $offset, $limit)
    {
        $sql = 'SELECT * FROM [nt:unstructured]
                    WHERE ISDESCENDANTNODE([nt:unstructured], ' . $this->manager->quote($this->staticPath) . ')
                        AND [nt:unstructured].[phpcr:class] = ' . $this->manager->quote($this->imageClass);

        if (strlen($caption)) {
            $sql.= '
                AND [nt:unstructured].caption LIKE ' . $this->manager->quote($caption.'%');
        }

        $query = $this->manager->createQuery($sql, QueryInterface::JCR_SQL2);
        $query->setFirstResult($offset);
        $query->setMaxResults($limit);
        return $this->manager->getDocumentsByPhpcrQuery($query->getPhpcrQuery());
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
        $query->setFirstResult($offset);
        $query->setMaxResults($limit);
        return $this->manager->getDocumentsByPhpcrQuery($query->getPhpcrQuery());
    }
}
