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

use Symfony\Cmf\Bundle\CreateBundle\Security\AccessCheckerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\HttpFoundation\Response;

use FOS\RestBundle\View\ViewHandlerInterface;
use FOS\RestBundle\View\View;

use Midgard\CreatePHP\Metadata\RdfTypeFactory;
use Midgard\CreatePHP\RestService;
use Midgard\CreatePHP\RdfMapperInterface;
use Midgard\CreatePHP\Helper\NamespaceHelper;

/**
 * Controller to handle content update callbacks.
 */
class RestController
{
    /**
     * @var ViewHandlerInterface
     */
    protected $viewHandler;

    /**
     * @var RdfMapperInterface
     */
    protected $rdfMapper;

    /**
     * @var RdfTypeFactory
     */
    protected $typeFactory;

    /**
     * @var RestService
     */
    protected $restHandler;

    /**
     * @var AccessCheckerInterface
     */
    protected $accessChecker;

    /**
     * @param ViewHandlerInterface   $viewHandler
     * @param RdfMapperInterface     $rdfMapper
     * @param RdfTypeFactory         $typeFactory
     * @param RestService            $restHandler
     * @param AccessCheckerInterface $accessChecker
     */
    public function __construct(
        ViewHandlerInterface $viewHandler,
        RdfMapperInterface $rdfMapper,
        RdfTypeFactory $typeFactory,
        RestService $restHandler,
        AccessCheckerInterface $accessChecker
    ) {
        $this->viewHandler = $viewHandler;
        $this->rdfMapper = $rdfMapper;
        $this->typeFactory = $typeFactory;
        $this->restHandler = $restHandler;
        $this->accessChecker = $accessChecker;
    }

    protected function getModelBySubject(Request $request, $subject)
    {
        $model = $this->rdfMapper->getBySubject($subject);
        if (empty($model)) {
            throw new NotFoundHttpException($subject.' not found');
        }

        return $model;
    }

    /**
     * Handle document PUT (update)
     *
     * @param Request $request
     * @param string  $subject URL of the subject, ie: cms/simple/news/news-name
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function putDocumentAction(Request $request, $subject)
    {
        if (!$this->accessChecker->check($request)) {
            throw new AccessDeniedException();
        }

        $model = $this->getModelBySubject($request, $subject);
        $type = $this->typeFactory->getTypeByObject($model);

        $result = $this->restHandler->run($request->request->all(), $type, null, RestService::HTTP_PUT);
        $view = View::create($result)->setFormat('json');

        return $this->viewHandler->handle($view, $request);
    }

    /**
     * Handle document POST (creation)
     *
     * @param Request $request
     *
     * @return Response
     */
    public function postDocumentAction(Request $request)
    {
        if (!$this->accessChecker->check($request)) {
            throw new AccessDeniedException();
        }

        $rdfType = trim($request->request->get('@type'), '<>');
        $type = $this->typeFactory->getTypeByRdf($rdfType);

        $result = $this->restHandler->run($request->request->all(), $type, null, RestService::HTTP_POST);

        if (!is_null($result)) {
            $view = View::create($result)->setFormat('json');
            return $this->viewHandler->handle($view, $request);
        }

        return Response::create('The document could not be created', 500);
    }
}
