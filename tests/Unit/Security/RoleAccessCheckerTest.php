<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2017 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\CreateBundle\Tests\Unit\Security;

use Symfony\Cmf\Bundle\CreateBundle\Security\RoleAccessChecker;

class RoleAccessCheckerTest extends \PHPUnit_Framework_TestCase
{
    private $request;
    private $tokenStorage;
    private $authorizationChecker;
    private $checker;

    public function setUp()
    {
        $this->request = $this->prophesize('Symfony\Component\HttpFoundation\Request')->reveal();
        $this->tokenStorage = $this->prophesize('Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface');
        $this->authorizationChecker = $this->prophesize('Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface');
        $this->checker = new RoleAccessChecker('THE_ROLE', $this->tokenStorage->reveal(), $this->authorizationChecker->reveal());
    }

    public function testFalseWithoutSecurityServices()
    {
        $checker = new RoleAccessChecker('THE_ROLE');
        $this->assertFalse($checker->check($this->request), 'Always false without token storage');

        $checker = new RoleAccessChecker('THE_ROLE', $this->tokenStorage->reveal());
        $this->assertFalse($checker->check($this->request), 'Always false without authorization checker');
    }

    public function testFalseWithoutTokenAvailable()
    {
        $this->assertFalse($this->checker->check($this->request));
    }

    public function testGranted()
    {
        $token = $this->prophesize('Symfony\Component\Security\Authentication\Token\TokenInterface')->reveal();
        $this->tokenStorage->getToken()->willReturn($token);
        $this->authorizationChecker->isGranted('THE_ROLE')->willReturn(true);

        $this->assertTrue($this->checker->check($this->request));
    }

    public function testNotGranted()
    {
        $token = $this->prophesize('Symfony\Component\Security\Authentication\Token\TokenInterface')->reveal();
        $this->tokenStorage->getToken()->willReturn($token);
        $this->authorizationChecker->isGranted('THE_ROLE')->willReturn(false);

        $this->assertFalse($this->checker->check($this->request));
    }

    public function testException()
    {
        $token = $this->prophesize('Symfony\Component\Security\Authentication\Token\TokenInterface')->reveal();
        $this->tokenStorage->getToken()->willReturn($token);
        $this->authorizationChecker->isGranted('THE_ROLE')->willThrow(new \Exception());

        $this->assertFalse($this->checker->check($this->request));
    }
}
