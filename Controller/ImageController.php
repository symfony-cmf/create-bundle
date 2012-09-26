<?php

namespace Symfony\Cmf\Bundle\CreateBundle\Controller;

use Doctrine\Common\Persistence\ManagerRegistry;

use Imagine\Image\ImageInterface;

use FOS\Rest\Util\Codes;
use FOS\RestBundle\View\View;
use FOS\RestBundle\View\ViewHandlerInterface;

use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\FileBag;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

abstract class ImageController
{
    /**
     * @var \Doctrine\Common\Persistence\ObjectManager
     */
    protected $manager;

    /**
     * @var \Symfony\Component\Routing\RouterInterface
     */
    protected $router;

    /**
     * @var \FOS\RestBundle\View\ViewHandlerInterface
     */
    protected $viewHandler;

    /**
     * @var string
     */
    protected $imageClass;

    public function __construct(ManagerRegistry $registry, $managerName, RouterInterface $router, ViewHandlerInterface $viewHandler, $imageClass)
    {
        $this->manager = $registry->getManager($managerName);
        $this->router = $router;
        $this->viewHandler = $viewHandler;
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
     * @param UploadedFile $file
     * @return mixed name
     */
    abstract protected function generateName(UploadedFile $file);

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

            $name = $this->generateName($file);
            $ids[] = $id = $this->generateId($name);
            $image = $this->manager->find(null, $id);
            if ($image) {
                throw new HttpException(Codes::HTTP_CONFLICT, "An image with the name '$name' already exists and can therefore not be stored at '$id'");
            }

            $image = new $imageClass();
            $image->setId($id);

            $image->setName($file->getClientOriginalName());
            $image->setContent(fopen($file->getPathname(), 'r'));
            $image->setMimeType($file->getClientMimeType());
            $image->setTags(explode(',', $request->get('tags', array())));

            $this->manager->persist($image);
            $images[] = $image;
        }

        $this->manager->flush();

        return $this->generateUploadResponse($ids, $images, $files);
    }

    /**
     * Search for assets matching the query
     *
     * This function currently only returns some fixture data to try the editor
     *
     */
    public function searchAction(Request $request)
    {
        $offset = (int)$request->query->get('offset', 0);
        $limit = (int)$request->query->get('limit', 8);
        $images = $this->getImagesByName($request->query->get('searchInput'), $offset, $limit);

        $data = array();

        foreach ($images as $image) {
            $url = $this->router->generate('symfony_cmf_create_image_display', array('name' => $image->getName()), true);
            $data[] = array('url' => $url, 'alt' => $image->getName());
        }

        $data = array(
            'offset' => $offset,
            'total' => count($data),
            'assets' => $data,
        );

        $view = View::create($data);
        return $this->viewHandler->handle($view);
    }

    abstract protected function getImagesByName($name, $offset, $limit);

    /**
     * List Images from Repo
     *
     * This function currently only returns some fixture data to try the editor
     *
     */
    public function listAction(Request $request)
    {
        $tags = $request->query->get('tags');
        $tags = explode(',', $tags);

        $images = $this->getImagesByTag($tags, 0, -1);

        $data = array();

        foreach ($images as $image) {
            $url = $this->router->generate('symfony_cmf_create_image_display', array('name' => $image->getName()), true);
            $data[] = array('url' => $url, 'alt' => $image->getName());
        }

        $data = array(
            'assets' => $data,
        );

        $view = View::create($data);
        return $this->viewHandler->handle($view);
    }

    abstract protected function getImagesByTag(array $tags, $offset, $limit);

    public function showRelatedAction(Request $request)
    {
        $tags = $request->query->get('tags');
        $page = $request->query->get('page');

        $tags = explode(',', $tags);

        $lang = $request->getLocale();

        $data = $this->getPagesByTags($tags, $page, $lang);
        $data = array(
            'links' => $data,
        );

        $view = View::create($data);
        return $this->viewHandler->handle($view);
    }

    /**
     * Connect to Jackrabbit, and search pages by tags
     *
     * @param $tags array with stanbol references
     * @param $currentUrl string current url
     * @param $lang string language
     *
     * @return array with links to pages
     */
    protected function getPagesByTags($tags, $currentUrl, $lang)
    {
        $this->basePath = $this->basePath.'/'.$lang;

        foreach ($tags as $i => $tag) {
            $tags[$i] = 'referring.tags = ' . $this->dm->quote($tag);
        }

        $sql = 'SELECT routes.* FROM [nt:unstructured] AS routes';
        $sql .= ' INNER JOIN [nt:unstructured] AS referring ON referring.[jcr:uuid] = routes.[routeContent]';
        $sql .= ' WHERE (ISDESCENDANTNODE(routes, ' . $this->dm->quote($this->basePath) . ') OR ISSAMENODE(routes, ' . $this->dm->quote($this->basePath) . '))';
        $sql .= ' AND (' . implode(' OR ', $tags) . ')';
        $query = $this->dm->createQuery($sql, QueryInterface::JCR_SQL2);
        $query->setLimit(-1);
        $pages = $this->dm->getDocumentsByQuery($query);

        $links = array();
        foreach ($pages as $page) {
            if ($page instanceof RouteObjectInterface && $page->getRouteContent()) {
                $url = $this->router->generate('', array('_locale' => $lang, 'content' => $page->getRouteContent()), true);

                if (preg_replace('/^\/|\/$/', '', $url) !== preg_replace('/^\/|\/$/', '', $currentUrl)) {
                    $label = $page->getRouteContent()->title;
                    $links[] = array('url' => $url, 'label' => $label);
                }
            }
        }

        return $links;
    }
}