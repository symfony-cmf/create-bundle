<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2014 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\CreateBundle\Tests\Unit\Security;

use Symfony\Cmf\Bundle\CreateBundle\Security\RoleAccessChecker;

class RoleAccessCheckerTest extends \PHPUnit_Framework_TestCase
{
    private $request;

    public function setUp()
    {
        $this->request = $this->getMock('Symfony\Component\HttpFoundation\Request');
    }

    public function testNoContext()
    {
        $checker = new RoleAccessChecker('THE_ROLE');
        $this->assertFalse($checker->check($this->request));
    }

    public function testNoToken()
    {
        $context = $this->getMock('Symfony\Component\Security\Core\SecurityContextInterface');
        $checker = new RoleAccessChecker('THE_ROLE', $context);
        $this->assertFalse($checker->check($this->request));
    }

    public function testGranted()
    {
        $context = $this->getMock('Symfony\Component\Security\Core\SecurityContextInterface');
        $context
            ->expects($this->once())
            ->method('getToken')
            ->will($this->returnValue(true))
        ;
        $context
            ->expects($this->once())
            ->method('isGranted')
            ->with('THE_ROLE')
            ->will($this->returnValue(true))
        ;

        $checker = new RoleAccessChecker('THE_ROLE', $context);
        $this->assertTrue($checker->check($this->request));
    }

    public function testNotGranted()
    {
        $context = $this->getMock('Symfony\Component\Security\Core\SecurityContextInterface');
        $context
            ->expects($this->once())
            ->method('getToken')
            ->will($this->returnValue(true))
        ;
        $context
            ->expects($this->once())
            ->method('isGranted')
            ->with('THE_ROLE')
            ->will($this->returnValue(false))
        ;

        $checker = new RoleAccessChecker('THE_ROLE', $context);
        $this->assertFalse($checker->check($this->request));
    }

    public function testException()
    {
        $context = $this->getMock('Symfony\Component\Security\Core\SecurityContextInterface');
        $context
            ->expects($this->once())
            ->method('getToken')
            ->will($this->returnValue(true))
        ;
        $context
            ->expects($this->once())
            ->method('isGranted')
            ->with('THE_ROLE')
            ->will($this->throwException(new \Exception))
        ;

        $checker = new RoleAccessChecker('THE_ROLE', $context);
        $this->assertFalse($checker->check($this->request));
    }

}
