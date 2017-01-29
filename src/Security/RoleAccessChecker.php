<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2017 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\CreateBundle\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
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
     * @var TokenStorageInterface
     */
    protected $tokenStorage;

    /**
     * @var AuthorizationCheckerInterface
     */
    protected $authorizationChecker;

    /**
     * @var string the role name for the security check
     */
    protected $requiredRole;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param string                             $requiredRole         the role to check with the security
     *                                                                 context
     * @param TokenStorageInterface|null         $tokenStorage         if null is passed, check will always
     *                                                                 return false
     * @param AuthorizationCheckerInterface|null $authorizationChecker if null is passed, check will always
     *                                                                 return false
     * @param LoggerInterface                    $logger               the logger to log exceptions from the
     *                                                                 security context
     */
    public function __construct(
        $requiredRole,
        TokenStorageInterface $tokenStorage = null,
        AuthorizationCheckerInterface $authorizationChecker = null,
        LoggerInterface $logger = null
    ) {
        $this->requiredRole = $requiredRole;
        $this->tokenStorage = $tokenStorage;
        $this->authorizationChecker = $authorizationChecker;
        $this->logger = $logger;
    }

    /**
     * Actions may be performed if there is a securityContext having a token
     * and granting the required role.
     *
     * {@inheritdoc}
     */
    public function check(Request $request)
    {
        try {
            return $this->tokenStorage && $this->authorizationChecker
                && $this->tokenStorage->getToken()
                && $this->authorizationChecker->isGranted($this->requiredRole)
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
