<?php

namespace Symfony\Cmf\Bundle\CreateBundle\Controller;

use Doctrine\Common\Persistence\ObjectManager;
use Imagine\Image\ImageInterface;
use Symfony\Component\Routing\RouterInterface;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\FileBag;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;

abstract class ImageController
{
    protected $manager;
    protected $router;
    protected $imageClass;

    public function __construct(ObjectManager $manager, RouterInterface $router, $imageClass)
    {
        $this->manager = $manager;
        $this->router = $router;
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
    protected function generateUploadResponse(array $ids, array $images, FileBag $files)
    {
        $name = basename($ids[0]);

        return new RedirectResponse($this->router->generate('symfony_cmf_create_image_display', array('name' => $name)));
    }

    protected function validateImage($file)
    {
        return true;
    }

    public function displayAction($name)
    {
        $id = $this->generateId($name);
        $image = $this->manager->find($this->imageClass, $id);
        if (!$image) {
            throw new NotFoundHttpException("Image '$name' not found at '$id'");
        }

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
            $image->setContent(fopen($file->getPathname(), 'r'));
            $image->setMimeType($file->getClientMimeType());
            $image->setTags(explode(',', $request->get('tags', array())));

            $this->manager->persist($image);
        }

        $this->manager->flush();

        return $this->generateUploadResponse($ids, $images, $files);
    }
}