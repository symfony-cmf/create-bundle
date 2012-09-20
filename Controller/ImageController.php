<?php

namespace Symfony\Cmf\Bundle\CreateBundle\Controller;

use Doctrine\Common\Persistence\ObjectManager;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

abstract class ImageController
{
    protected $manager;

    public function __construct(ObjectManager $manager, $imageClass)
    {
        $this->manager = $manager;
        $this->imageClass = $imageClass;
    }

    /**
     * Generate an ID for the persistence manager
     *
     * @param string $name
     * @return mixed
     */
    abstract protected function generateId($name);

    /**
     * @param $file
     * @return mixed name
     */
    abstract protected function generateName($file);

    /**
     * Generate the response for the uploaded images
     *
     * @param array $ids
     * @param array $images
     * @param array $files
     * @return Response
     */
    abstract protected function generateUploadResponse(array $ids, array $images, array $files);

    protected function validateImage($file)
    {
        return true;
    }

    public function displayAction($id)
    {
        $image = $this->manager->find($this->imageClass, $this->generateId($id));

        $data = stream_get_contents($image->getContent());

        $response = new Response($data);
        $response->headers->set('Content-Type', $image->getMimeType());

        return $response;
    }

    public function uploadAction(Request $request)
    {
        $files = $request->files;

        $ids = $images = array();
        $imageClass = $this->imageClass;
        foreach ($files->all() as $file) {
            if (!$this->validateImage($file)) {
                continue;
            }
            $ids[] = $id = $this->generateId($this->generateName($file));
            $images[] = $image = $this->manager->find($imageClass, $id);
            if (!$image) {
                $image = new $imageClass();
                $image->setId($id);
            }

            $image->setName($file->getClientOriginalName());
            $image->setContent(file_get_contents($file->getPathname()));
            $image->setMimeType($file->getClientMimeType());
            $image->setTags(explode(',', $request->get('tags')));

            $this->manager->persist($image);
        }

        $this->manager->flush();

        return $this->generateUploadResponse($ids, $images, $files);
    }
}