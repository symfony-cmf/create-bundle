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

use Symfony\Cmf\Bundle\CoreBundle\Translatable\TranslatableInterface;
use Symfony\Cmf\Bundle\CreateBundle\Security\AccessCheckerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Response;

use FOS\RestBundle\View\ViewHandlerInterface;
use FOS\RestBundle\View\View;

use Midgard\CreatePHP\Metadata\RdfTypeFactory;
use Midgard\CreatePHP\RestService;
use Midgard\CreatePHP\RdfMapperInterface;

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
     * @var boolean
     */
    protected $forceRequestLocale;

    /**
     * @param ViewHandlerInterface   $viewHandler
     * @param RdfMapperInterface     $rdfMapper
     * @param RdfTypeFactory         $typeFactory
     * @param RestService            $restHandler
     * @param AccessCheckerInterface $accessChecker
     * @param boolean                $forceRequestLocale
     */
    public function __construct(
        ViewHandlerInterface $viewHandler,
        RdfMapperInterface $rdfMapper,
        RdfTypeFactory $typeFactory,
        RestService $restHandler,
        AccessCheckerInterface $accessChecker,
        $forceRequestLocale
    ) {
        $this->viewHandler = $viewHandler;
        $this->rdfMapper = $rdfMapper;
        $this->typeFactory = $typeFactory;
        $this->restHandler = $restHandler;
        $this->accessChecker = $accessChecker;
        $this->forceRequestLocale = $forceRequestLocale;
    }

    protected function getModelBySubject(Request $request, $subject)
    {
        $model = $this->rdfMapper->getBySubject($subject);
        if (empty($model)) {
            throw new NotFoundHttpException($subject.' not found');
        }

        if ($this->forceRequestLocale && $model instanceof TranslatableInterface) {
            $model->setLocale($request->getLocale());
        }

        return $model;
    }

    /**
     * Handle arbitrary methods with the RestHandler.
     *
     * Except for the PUT operation to update a document, operations are
     * registered as workflows.
     *
     * @param Request $request
     * @param string  $subject URL of the subject, ie: /cms/simple/news/news-name
     *
     * @return Response
     *
     * @throws AccessDeniedException If the action is not allowed by the access
     *                               checker.
     *
     * @see RestService::run
     *
     * @since 1.2
     */
    public function updateDocumentAction(Request $request, $subject)
    {
        if (!$this->accessChecker->check($request)) {
            throw new AccessDeniedException();
        }

        $model = $this->getModelBySubject($request, $subject);
        $type = $this->typeFactory->getTypeByObject($model);

        $result = $this->restHandler->run($request->request->all(), $type, $subject, strtolower($request->getMethod()));
        $view = View::create($result)->setFormat('json');

        return $this->viewHandler->handle($view, $request);
    }

    /**
     * Handle document POST (creation)
     *
     * @param Request $request
     *
     * @return Response
     *
     * @throws AccessDeniedException If the action is not allowed by the access
     *                               checker.
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

        return Response::create('The document was not created', 500);
    }

    /**
     * @deprecated Use updateDocumentAction
     */
    public function putDocumentAction(Request $request, $subject)
    {
        return self::updateDocumentAction($request, $subject);
    }

    /**
     * @deprecated Use updateDocumentAction
     */
    public function deleteDocumentAction(Request $request, $subject)
    {
        return self::updateDocumentAction($request, $subject);
    }

    /**
     * Get available Workflows for a document.
     *
     * @param Request $request
     * @param string  $subject
     *
     * @return Response
     *
     * @throws AccessDeniedException If getting workflows for this document is
     *                               not allowed by the access checker.
     */
    public function workflowsAction(Request $request, $subject)
    {
        if (!$this->accessChecker->check($request)) {
            throw new AccessDeniedException();
        }

        $result = $this->restHandler->getWorkflows($subject);
        $view = View::create($result)->setFormat('json');

        return $this->viewHandler->handle($view, $request);
    }

    /**
     * DEPRECATED: Check if the action can be performed.
     *
     * @deprecated use $this->accessChecker->check instead. This method always
     *             denies access.
     *
     * @throws AccessDeniedException Always denies access.
     */
    protected function performSecurityChecks()
    {
        throw new AccessDeniedException();
    }

}
