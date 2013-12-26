<?php

namespace Symfony\Cmf\Bundle\CreateBundle\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\SecurityContextInterface;

/**
 * An access check for the create bundle controllers that can decide whether
 * the current user is allowed to edit.
 *
 * The security context is optional to not fail with an exception if the
 * controller is loaded in a context without a firewall.
 */
class RoleAccessChecker implements AccessCheckerInterface
{
    /**
     * @var SecurityContextInterface
     */
    protected $securityContext;

    /**
     * @var string the role name for the security check
     */
    protected $requiredRole;

    /**
     * @param string                        $requiredRole The role to check
     *      with the securityContext.
     * @param SecurityContextInterface|null $securityContext The security
     *      context to use to check for the role. If this is null, the security
     *      check will always return false.
     */
    public function __construct(
        $requiredRole,
        SecurityContextInterface $securityContext = null
    ) {
        $this->requiredRole = $requiredRole;
        $this->securityContext = $securityContext;
    }

    /**
     * Actions may be performed if there is a securityContext having a token
     * and granting the required role.
     *
     * {@inheritDoc}
     */
    public function check(Request $request)
    {
        try {
            return $this->securityContext
                && $this->securityContext->getToken()
                && $this->securityContext->isGranted($this->requiredRole)
            ;
        } catch(\Exception $e) {
            // ignore and return false
        }

        return false;
    }
}
