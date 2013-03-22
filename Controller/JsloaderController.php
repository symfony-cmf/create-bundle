<?php

namespace Symfony\Cmf\Bundle\CreateBundle\Controller;

use FOS\RestBundle\View\ViewHandlerInterface,
    FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * This controller includes the correct twig file to bootstrap the javascript
 * files of create.js and its dependencies.
 */
class JsloaderController
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
     * @var string
     */
    private $stanbolUrl;

    /**
     * @var Boolean
     */
    private $fixedToolbar;

    /**
     * @var array
     */
    private $plainTextTypes;

    /**
     * @var array
     */
    private $createRoutesForTypes;

    /**
     * @var array locales of the application
     */
    private $locales;

    /**
     * @var string content prefix in the repository path, like /cms/content
     */
    private $contentPrefix;

    /**
     * @var string routes prefix in the repository path, like /cms/routes
     */
    private $routesPrefix;


    /**
     * Create the Controller
     *
     * @param ViewHandlerInterface $viewHandler view handler
     * @param string $stanbolUrl the url to use for the semantic enhancer stanbol
     * @param string $imageClass used to determine whether image upload should be activated
     * @param Boolean $fixedToolbar whether the hallo toolbar is fixed or floating
     * @param array $plainTextTypes RDFa types to edit in raw text only
     * @param array $createRoutesForTypes types for which it is needed to create a route
     * @param string $requiredRole the role name for the security check
     * @param SecurityContextInterface $securityContext
     * @param array $locales the locales of the application
     * @param string $contentPrefix content prefix in the repository path
     * @param string $routesPrefix routes prefix in the repository path
     */
    public function __construct(
        ViewHandlerInterface $viewHandler,
        $stanbolUrl,
        $imageClass,
        $fixedToolbar = true,
        array $plainTextTypes = array(),
        array $createRoutesForTypes = array(),
        $requiredRole = "IS_AUTHENTICATED_ANONYMOUSLY",
        SecurityContextInterface $securityContext = null,
        array $locales,
        $contentPrefix,
        $routesPrefix
    ) {
        $this->viewHandler = $viewHandler;
        $this->stanbolUrl = $stanbolUrl;
        $this->imageClass = $imageClass;
        $this->fixedToolbar = $fixedToolbar;
        $this->plainTextTypes = $plainTextTypes;
        $this->createRoutesForTypes = $createRoutesForTypes;
        $this->requiredRole = $requiredRole;
        $this->securityContext = $securityContext;
        $this->locales = $locales;
        $this->contentPrefix = $contentPrefix;
        $this->routesPrefix = $routesPrefix;
    }

    /**
     * Render js inclusion for create.js and dependencies and bootstrap code.
     *
     * The hallo editor is bundled with create.js and available automatically.
<<<<<<< HEAD
     * To use aloha, you need to download the zip, as explained in step 8 of
     * the README.
=======
>>>>>>> master
     *
     * When using hallo, the controller can include the compiled js files from
     * hallo's examples folder or use the assetic coffee filter.
     * When developing hallo, make sure to use the coffee filter (pass 'hallo-coffee' as
     * editor).
     *
     * To use another editor simply create a template following the naming below:
     *   SymfonyCmfCreateBundle::includejsfiles-%editor%.html.twig
     * and pass the appropriate parameter.
     *
     * @param string $editor the name of the editor to load, currently only
     *      hallo and hallo-coffee are supported
     */
    public function includeJSFilesAction($editor = 'hallo')
    {
        if ($this->securityContext && false === $this->securityContext->isGranted($this->requiredRole)) {
            return new Response('');
        }

        $view = new View();

        $view->setTemplate(sprintf('SymfonyCmfCreateBundle::includejsfiles-%s.html.twig', $editor));



        $view->setData(array(
                'cmfCreateStanbolUrl' => $this->stanbolUrl,
                'cmfCreateImageUploadEnabled' => (boolean) $this->imageClass,
                'cmfCreateHalloFixedToolbar' => (boolean) $this->fixedToolbar,
                'cmfCreateHalloPlainTextTypes' => json_encode($this->plainTextTypes),
                'cmfCreateCreateRoutesTypes' => json_encode($this->createRoutesForTypes),
                'cmfCreateLocales' => json_encode($this->locales),
                'cmfCreateContentPrefix' => $this->contentPrefix,
                'cmfCreateRoutesPrefix' => $this->routesPrefix
            )
        );

        return $this->viewHandler->handle($view);
    }
}
