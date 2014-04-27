<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2014 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\CreateBundle\Controller;

use Doctrine\Common\Persistence\ManagerRegistry;
use FOS\RestBundle\View\View;
use FOS\RestBundle\View\ViewHandlerInterface;
use Symfony\Cmf\Bundle\CreateBundle\Security\AccessCheckerInterface;
use Symfony\Cmf\Bundle\MediaBundle\Controller\FileController;
use Symfony\Cmf\Bundle\MediaBundle\File\UploadFileHelperInterface;
use Symfony\Cmf\Bundle\MediaBundle\MediaManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class ImageController extends FileController
{
    /**
     * @var ViewHandlerInterface
     */
    protected $viewHandler;

    /**
     * @var AccessCheckerInterface
     */
    protected $accessChecker;

    /**
     * @param ManagerRegistry           $registry
     * @param string                    $managerName
     * @param string                    $class            FQN of image class
     * @param string                    $rootPath         Repository path where the
     *                                                    images are located
     * @param MediaManagerInterface     $mediaManager
     * @param UploadFileHelperInterface $uploadFileHelper
     * @param ViewHandlerInterface      $viewHandler
     * @param AccessCheckerInterface    $accessChecker
     */
    public function __construct(
        ManagerRegistry $registry,
        $managerName,
        $class,
        $rootPath = '/',
        MediaManagerInterface $mediaManager,
        UploadFileHelperInterface $uploadFileHelper,
        ViewHandlerInterface $viewHandler,
        AccessCheckerInterface $accessChecker
    ) {
        if (!is_subclass_of($class, 'Symfony\Cmf\Bundle\MediaBundle\ImageInterface')) {
            throw new \InvalidArgumentException(sprintf(
                'The class "%s" does not implement Symfony\Cmf\Bundle\MediaBundle\ImageInterface',
                $class
            ));
        }

        // initialize parent. the security parameters should not be used as we overwrite
        // the checkSecurityUpload method
        parent::__construct($registry, $managerName, $class, $rootPath,
            $mediaManager, $uploadFileHelper, 'THISSHOULDNEVERGETUSED', null
        );

        $this->viewHandler = $viewHandler;
        $this->accessChecker = $accessChecker;

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
        $offset = (int) $request->query->get('offset', 0);
        $limit = (int) $request->query->get('limit', 8);
        $query = $request->query->get('query');
        $images = $this->getImagesByCaption($query, $offset, $limit);

        return $this->processResults($images, $offset);
    }

    /**
     * Get images by a specified caption
     *
     * @param string $name
     * @param int    $offset
     * @param int    $limit
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

    protected function checkSecurityUpload(Request $request)
    {
        if (!$this->accessChecker->check($request)) {
            throw new AccessDeniedException();
        }
    }
}
