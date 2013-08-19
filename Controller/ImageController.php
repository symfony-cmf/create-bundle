<?php

namespace Symfony\Cmf\Bundle\CreateBundle\Controller;

use Doctrine\Common\Persistence\ManagerRegistry;
use FOS\RestBundle\View\View;
use FOS\RestBundle\View\ViewHandlerInterface;
use Symfony\Cmf\Bundle\MediaBundle\Controller\FileController;
use Symfony\Cmf\Bundle\MediaBundle\File\UploadFileHelper;
use Symfony\Cmf\Bundle\MediaBundle\MediaManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\SecurityContextInterface;

class ImageController extends FileController
{
    protected $viewHandler;

    /**
     * @param ManagerRegistry          $registry
     * @param string                   $managerName
     * @param string                   $class            fully qualified class
     *                                                   name of file
     * @param string                   $rootPath         path where the
     *                                                   filesystem is located
     * @param UploadFileHelper         $uploadFileHelper
     * @param RouterInterface          $router
     * @param ViewHandlerInterface     $viewHandler
     * @param string                   $requiredRole     the role name for the
     *                                                   security check
     * @param SecurityContextInterface $securityContext
     */
    public function __construct(
        ManagerRegistry $registry,
        $managerName,
        $class,
        $rootPath = '/',
        MediaManagerInterface $mediaManager,
        UploadFileHelper $uploadFileHelper,
        ViewHandlerInterface $viewHandler,
        $requiredRole = "IS_AUTHENTICATED_ANONYMOUSLY",
        SecurityContextInterface $securityContext = null
    ) {
        $this->viewHandler      = $viewHandler;

        if (!is_subclass_of($class, 'Symfony\Cmf\Bundle\MediaBundle\ImageInterface')) {
            throw new \InvalidArgumentException(sprintf(
                'The class "%s" does not implement Symfony\Cmf\Bundle\MediaBundle\ImageInterface',
                $class
            ));
        }

        parent::__construct($registry, $managerName, $class, $rootPath,
            $mediaManager, $uploadFileHelper, $requiredRole, $securityContext);
    }

    private function processResults($images, $offset)
    {
        $data = array(
            'offset' => $offset,
            'total' => count($images),
            'assets' => $images
        );

        $view = View::create($data);
        return $this->viewHandler->handle($view);
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
        $query = $request->query->get('query');
        $images = $this->getImagesByCaption($query, $offset, $limit);

        return $this->processResults($images, $offset);
    }

    /**
     * Get images by a specified caption
     *
     * @param string $name
     * @param int $offset
     * @param int $limit
     *
     * @return array
     */
    protected function getImagesByCaption($name, $offset, $limit)
    {
        $images = $this->getObjectManager()->getRepository($this->class)
            ->setRootPath($this->rootPath)
            ->searchImages($name, $limit, $offset);

        return $images ? array_values($images->toArray()) : array();
    }

    /**
     * TODO: returns empty response
     *
     * @param Request $request
     *
     * @return Response
     */
    public function showRelatedAction(Request $request)
    {
        $links = array();
        $data = array(
            'links' => $links,
        );

        $view = View::create($data);
        return $this->viewHandler->handle($view);
    }
}