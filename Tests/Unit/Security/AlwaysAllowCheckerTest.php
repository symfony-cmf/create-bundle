<?php

namespace Symfony\Cmf\Bundle\CreateBundle\Tests\Unit\Security;

use Symfony\Cmf\Bundle\CreateBundle\Security\AlwaysAllowChecker;

class AlwaysAllowCheckerTest extends \PHPUnit_Framework_TestCase
{
    public function testAllow()
    {
        $request = $this->getMock('Symfony\Component\HttpFoundation\Request');
        $checker = new AlwaysAllowChecker();
        $this->assertTrue($checker->check($request));
    }
}
