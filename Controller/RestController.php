<?php

namespace Symfony\Cmf\Bundle\CreateBundle\Controller;

use Symfony\Component\HttpFoundation\Request,
    Symfony\Component\HttpKernel\Exception\NotFoundHttpException,
    Symfony\Component\Security\Core\Exception\AccessDeniedException,
    Symfony\Component\Security\Core\SecurityContextInterface,
    Symfony\Component\HttpFoundation\Response;

use FOS\RestBundle\View\ViewHandlerInterface,
    FOS\RestBundle\View\View;

use Midgard\CreatePHP\Metadata\RdfTypeFactory,
    Midgard\CreatePHP\RestService,
    Midgard\CreatePHP\RdfMapperInterface,
    Midgard\CreatePHP\Helper\NamespaceHelper;

/**
 * Controller to handle content update callbacks
 */
class RestController
{
    /**
     * @var SecurityContextInterface
     */
    protected $securityContext;

    /**
     * @var ViewHandlerInterface
     */
    protected $viewHandler;

    /**
     * @var string the role name for the security check
     */
    protected $requiredRole;

    /**
     * @var RdfMapperInterface
     */
    protected $rdfMapper;

    /**
     * @var string
     */
    protected $name;

    /**
     * @param \FOS\RestBundle\View\ViewHandlerInterface $viewHandler
     * @param \Midgard\CreatePHP\RdfMapperInterface $rdfMapper
     * @param \Midgard\CreatePHP\Metadata\RdfTypeFactory $typeFactory
     * @param \Midgard\CreatePHP\RestService $restHandler
     * @param string $requiredRole the role to check with the securityContext
     *      (if you pass one), defaults to everybody: IS_AUTHENTICATED_ANONYMOUSLY
     * @param \Symfony\Component\Security\Core\SecurityContextInterface|null $securityContext
     *      the security context to use to check for the role. No security
     *      check if this is null
     *
     */
    public function __construct(
        ViewHandlerInterface $viewHandler,
        RdfMapperInterface $rdfMapper,
        RdfTypeFactory $typeFactory,
        RestService $restHandler,
        $requiredRole = "IS_AUTHENTICATED_ANONYMOUSLY",
        SecurityContextInterface $securityContext = null
    ) {
        $this->viewHandler = $viewHandler;
        $this->rdfMapper = $rdfMapper;
        $this->typeFactory = $typeFactory;
        $this->restHandler = $restHandler;
        $this->requiredRole = $requiredRole;
        $this->securityContext = $securityContext;
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
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param string $subject URL of the subject, ie: cms/simple/news/news-name
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function putDocumentAction(Request $request, $subject)
    {
        $this->performSecurityChecks();

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
     * @return Response
     */
    public function postDocumentAction(Request $request)
    {
        $this->performSecurityChecks();

        $rdfType = trim($request->request->get('@type'), '<>');

        $type = $this->typeFactory->getTypeByRdf($rdfType);

        $result = null;
        try {
            $result = $this->restHandler->run($request->request->all(), $type, null, RestService::HTTP_POST);
        } catch (\Exception $e) {
            return Response::create("The document '$rdfType' could not be created: " . $e->getMessage(), 500);
        }

        if (!is_null($result)) {
            $view = View::create($result)->setFormat('json');
            return $this->viewHandler->handle($view, $request);
        }

        return Response::create("The document '$rdfType' could not be created", 500);
    }

    /**
     * Check if the action can be performed
     *
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     */
    protected function performSecurityChecks()
    {
        if ($this->securityContext && false === $this->securityContext->isGranted($this->requiredRole)) {
            throw new AccessDeniedException();
        }
    }
}
