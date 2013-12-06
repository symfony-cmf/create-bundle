<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2013 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


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
 * Controller to handle content update callbacks.
 *
 * The security context is optional to not fail with an exception if the
 * controller is loaded in a context without a firewall.
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
     * @param ViewHandlerInterface          $viewHandler
     * @param RdfMapperInterface            $rdfMapper
     * @param RdfTypeFactory                $typeFactory
     * @param RestService                   $restHandler
     * @param string|boolean                $requiredRole The role to check
     *      with the securityContext (if you pass one), defaults to everybody.
     *      No security check if false.
     * @param SecurityContextInterface|null $securityContext The security
     *      context to use to check for the role.
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
     * @param Request $request
     * @param string  $subject URL of the subject, ie: cms/simple/news/news-name
     *
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
     *
     * @return Response
     */
    public function postDocumentAction(Request $request)
    {
        $this->performSecurityChecks();

        $rdfType = trim($request->request->get('@type'), '<>');
        $type = $this->typeFactory->getTypeByRdf($rdfType);

        $result = $this->restHandler->run($request->request->all(), $type, null, RestService::HTTP_POST);

        if (!is_null($result)) {
            $view = View::create($result)->setFormat('json');
            return $this->viewHandler->handle($view, $request);
        }

        return Response::create('The document could not be created', 500);
    }

    /**
     * Actions may be performed if the requiredRole is set to false (completely
     * disable security check) or if there is a securityContext and it grants
     * the required role.
     *
     * @throws AccessDeniedException If the current user is not allowed to edit
     */
    protected function performSecurityChecks()
    {
        if (false === $this->requiredRole) {
            // security check is disabled
            return;
        }

        if (!$this->securityContext
            || !$this->securityContext->getToken()
            || !$this->securityContext->isGranted($this->requiredRole)
        ) {
            throw new AccessDeniedException();
        }
    }
}
