<?php

namespace Symfony\Cmf\Bundle\CreateBundle\Controller;

use FOS\RestBundle\View\ViewHandlerInterface,
    FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\SecurityContextInterface;

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
    private $coffee;

    /**
     * @var Boolean
     */
    private $fixedToolbar;

    /**
     * @var array
     */
    private $plainTextTypes;


    /**
     * Create the Controller
     *
     * When using hallo, the controller can include the compiled js files from
     * hallo's examples folder or use the assetic coffee filter.
     * When developing hallo, make sure to use the coffee filter.
     *
     * @param ViewHandlerInterface $viewHandler view handler
     * @param string $stanbolUrl the url to use for the semantic enhancer stanbol
     * @param string $imageClass used to determine whether image upload should be activated
     * @param Boolean $useCoffee whether assetic is set up to use coffee script
     * @param Boolean $fixedToolbar whether the hallo toolbar is fixed or floating
     * @param array $plainTextTypes RDFa types to edit in raw text only
     * @param string $requiredRole
     * @param SecurityContextInterface $securityContext
     */
    public function __construct(
        ViewHandlerInterface $viewHandler,
        $stanbolUrl,
        $imageClass,
        $useCoffee = false,
        $fixedToolbar = true,
        $plainTextTypes = array(),
        $requiredRole = "IS_AUTHENTICATED_ANONYMOUSLY",
        SecurityContextInterface $securityContext = null
    ) {
        $this->viewHandler = $viewHandler;
        $this->stanbolUrl = $stanbolUrl;
        $this->imageClass = $imageClass;
        $this->coffee = $useCoffee;
        $this->fixedToolbar = $fixedToolbar;
        $this->plainTextTypes = $plainTextTypes;

        $this->requiredRole = $requiredRole;
        $this->securityContext = $securityContext;
    }

    /**
     * Render js inclusion for create.js and dependencies and bootstrap code.
     *
     * THe hallo editor is bundled with create.js and available automatically.
     * To use aloha, you need to download the zip, as explained in step 8 of
     * the README.
     *
     * @param string $editor the name of the editor to load, currently hallo and aloha are supported
     */
    public function includeJSFilesAction($editor = 'hallo')
    {
        if ($this->securityContext && false === $this->securityContext->isGranted($this->requiredRole)) {
            return new Response('');
        }

        // We could inject a list of names to template mapping for this
        // to allow adding other editors without changing this bundle

        $view = new View();
        switch ($editor) {
            case 'hallo':
                if ($this->coffee) {
                    $view->setTemplate('SymfonyCmfCreateBundle::includecoffeefiles-hallo.html.twig');
                } else {
                    $view->setTemplate('SymfonyCmfCreateBundle::includejsfiles-hallo.html.twig');
                }
                break;
            case 'aloha':
                $view->setTemplate('SymfonyCmfCreateBundle::includejsfiles-aloha.html.twig');
                break;
            default:
                throw new \InvalidArgumentException("Unknown editor '$editor' requested");
        }

        $view->setData(array(
                'cmfCreateStanbolUrl' => $this->stanbolUrl,
                'cmfCreateImageUploadEnabled' => (boolean) $this->imageClass,
                'cmfCreateHalloFixedToolbar' => (boolean) $this->fixedToolbar,
                'cmfCreateHalloPlainTextTypes' => json_encode($this->plainTextTypes))
        );

        return $this->viewHandler->handle($view);
    }
}
