<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2016 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\CreateBundle\Tests\Unit\Security;

use Symfony\Cmf\Bundle\CreateBundle\Security\AlwaysAllowChecker;

class AlwaysAllowCheckerTest extends \PHPUnit_Framework_TestCase
{
    public function testAlwaysAllows()
    {
        $request = $this->prophesize('Symfony\Component\HttpFoundation\Request');

        $checker = new AlwaysAllowChecker();
        $this->assertTrue($checker->check($request->reveal()));
    }
}
