<?php

namespace Oro\Bundle\UserBundle\Tests\Security;

use Oro\Bundle\UserBundle\Security\UserProvider;
use Symfony\Component\Security\Core\Encoder\EncoderFactory;
use Symfony\Component\Security\Core\Encoder\MessageDigestPasswordEncoder;

class UserProviderTest extends \PHPUnit_Framework_TestCase
{
    const USER_CLASS = 'Pim\Bundle\UserBundle\Entity\UserInterface';

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $userManager;

    /**
     * @var UserProvider
     */
    private $userProvider;

    protected function setUp()
    {
        if (!interface_exists('Doctrine\Common\Persistence\ObjectManager')) {
            $this->markTestSkipped('Doctrine Common has to be installed for this test to run.');
        }

        $ef = new EncoderFactory([static::USER_CLASS => new MessageDigestPasswordEncoder('sha512')]);
        $class = $this->getMock('Doctrine\Common\Persistence\Mapping\ClassMetadata');
        $om = $this->getMock('Doctrine\Common\Persistence\ObjectManager');
        $repository = $this->getMock('Doctrine\Common\Persistence\ObjectRepository');

        $om->expects($this->any())
            ->method('getRepository')
            ->withAnyParameters()
            ->will($this->returnValue($repository));

        $om->expects($this->any())
            ->method('getClassMetadata')
            ->with($this->equalTo(static::USER_CLASS))
            ->will($this->returnValue($class));

        $this->userManager = $this->getMock(
            'Oro\Bundle\UserBundle\Entity\UserManager',
            ['findUserBy', 'findUserByUsernameOrEmail', 'getClass'],
            [static::USER_CLASS, $om, $ef]
        );

        $this->userManager
            ->expects($this->any())
            ->method('getClass')
            ->will($this->returnValue(static::USER_CLASS));

        $this->userProvider = new UserProvider($this->userManager);
    }

    /**
     * @expectedException Symfony\Component\Security\Core\Exception\UsernameNotFoundException
     */
    public function testLoadUserByInvalidUsername()
    {
        $this->userManager
            ->expects($this->once())
            ->method('findUserByUsernameOrEmail')
            ->with($this->equalTo('foobar'))
            ->will($this->returnValue(null));

        $this->userProvider->loadUserByUsername('foobar');
    }

    public function testRefreshUserBy()
    {
        $user = $this->getMockBuilder('Pim\Bundle\UserBundle\Entity\UserInterface')
            ->setMethods(['getId'])
            ->getMock();

        $user->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(123));

        $refreshedUser = $this->getMock('Pim\Bundle\UserBundle\Entity\UserInterface');

        $this->userManager
            ->expects($this->once())
            ->method('findUserBy')
            ->with(['id' => 123])
            ->will($this->returnValue($refreshedUser));

        $this->assertSame($refreshedUser, $this->userProvider->refreshUser($user));
    }

    /**
     * @expectedException Symfony\Component\Security\Core\Exception\UsernameNotFoundException
     */
    public function testRefreshDeleted()
    {
        $user = $this->getMock('Pim\Bundle\UserBundle\Entity\UserInterface');

        $this->userManager
            ->expects($this->once())
            ->method('findUserBy')
            ->will($this->returnValue(null));

        $this->userProvider->refreshUser($user);
    }

    /**
     * @expectedException Symfony\Component\Security\Core\Exception\UnsupportedUserException
     */
    public function testRefreshInvalidUser()
    {
        $user = $this->getMockForAbstractClass('Symfony\Component\Security\Core\User\UserInterface');

        $this->userProvider->refreshUser($user);
    }

    public function testSupportsClass()
    {
        $this->assertTrue($this->userProvider->supportsClass(static::USER_CLASS));
    }
}
