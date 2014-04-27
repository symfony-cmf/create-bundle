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

/**
 * An access check for the create bundle controllers that can decide whether
 * the current user is allowed to edit.
 */
interface AccessCheckerInterface
{
    /**
     * Decide whether access should be granted, without ever throwing an
     * exception.
     *
     * @param Request $request The request in question, to take into account if
     *                         needed.
     *
     * @return boolean true if access is granted, false otherwise
     */
    public function check(Request $request);
}
