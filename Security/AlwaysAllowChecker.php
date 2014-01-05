<?php

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
     * {@inheritDoc}
     *
     * @return boolean true
     */
    public function check(Request $request)
    {
        return true;
    }
}
