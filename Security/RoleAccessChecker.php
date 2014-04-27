<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2014 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\CreateBundle\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Psr\Log\LoggerInterface;

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
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param string                        $requiredRole    The role to check with the security
     *                                                       context.
     * @param SecurityContextInterface|null $securityContext Context to get the current user from
     *                                                       and check if he has the required role.
     *                                                       If it is null, the security check will
     *                                                       always return false.
     * @param LoggerInterface               $logger          The logger to log exceptions from the
     *                                                       security context.
     */
    public function __construct(
        $requiredRole,
        SecurityContextInterface $securityContext = null,
        LoggerInterface $logger = null
    ) {
        $this->requiredRole = $requiredRole;
        $this->securityContext = $securityContext;
        $this->logger = $logger;
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
        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->error($e, array('exception' => $e));
            }
            // ignore and return false
        }

        return false;
    }
}
