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

/**
 * An access check for the create bundle controllers that always returns true.
 */
class AlwaysAllowChecker implements AccessCheckerInterface
{
    /**
     * Always returns true.
     *
     * {@inheritdoc}
     *
     * @return bool true
     */
    public function check(Request $request)
    {
        return true;
    }
}
