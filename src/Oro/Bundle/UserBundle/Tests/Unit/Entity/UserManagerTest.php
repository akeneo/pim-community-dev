<?php

namespace Oro\Bundle\UserBundle\Tests\Entity;

use Akeneo\Component\StorageUtils\Factory\SimpleFactory;
use Oro\Bundle\UserBundle\Entity\Role;
use Oro\Bundle\UserBundle\Entity\UserManager;
use Pim\Bundle\UserBundle\Entity\UserInterface;
use Symfony\Component\Security\Core\Encoder\EncoderFactory;
use Symfony\Component\Security\Core\Encoder\MessageDigestPasswordEncoder;

class UserManagerTest extends \PHPUnit_Framework_TestCase
{
    const USER_CLASS = 'Pim\Bundle\UserBundle\Entity\UserInterface';
    const TEST_NAME = 'Jack';
    const TEST_EMAIL = 'jack@jackmail.net';

    /**
     * @var UserInterface
     */
    protected $user;

    /**
     * @var UserManager
     */
    protected $userManager;

    /**
     * @var SimpleFactory
     */
    protected $userFactory;

    protected $om;
    protected $repository;

    protected function setUp()
    {
        if (!interface_exists('Doctrine\Common\Persistence\ObjectManager')) {
            $this->markTestSkipped('Doctrine Common has to be installed for this test to run.');
        }

        $ef = new EncoderFactory([static::USER_CLASS => new MessageDigestPasswordEncoder('sha512')]);
        $class = $this->getMock('Doctrine\Common\Persistence\Mapping\ClassMetadata');

        $this->om = $this->getMock('Doctrine\Common\Persistence\ObjectManager');
        $this->repository = $this->getMock('Doctrine\Common\Persistence\ObjectRepository');

        $this->om
            ->expects($this->any())
            ->method('getRepository')
            ->withAnyParameters()
            ->will($this->returnValue($this->repository));

        $this->om
            ->expects($this->any())
            ->method('getClassMetadata')
            ->with($this->equalTo(static::USER_CLASS))
            ->will($this->returnValue($class));

        $class->expects($this->any())
            ->method('getName')
            ->will($this->returnValue(static::USER_CLASS));

        $this->userManager = new UserManager(static::USER_CLASS, $this->om, $ef);
        $this->userFactory = new SimpleFactory(static::USER_CLASS);
    }

    public function testGetClass()
    {
        $this->assertEquals(static::USER_CLASS, $this->userManager->getClass());
    }

    public function testCreateUser()
    {
        $this->assertInstanceof(static::USER_CLASS, $this->getUser());
    }

    public function testDeleteUser()
    {
        $user = $this->getUser();

        $this->om->expects($this->once())->method('remove')->with($this->equalTo($user));
        $this->om->expects($this->once())->method('flush');

        $this->userManager->deleteUser($user);
    }

    public function testUpdateUser()
    {
        $user = $this->getUser()
            ->setUsername(self::TEST_NAME)
            ->setEmail(self::TEST_EMAIL)
            ->setPlainPassword('password');

        $this->om->expects($this->once())->method('persist')->with($this->equalTo($user));
        $this->om->expects($this->once())->method('flush');

        $this->repository
            ->expects($this->once())
            ->method('findOneBy')
            ->with($this->equalTo(['role' => User::ROLE_DEFAULT]))
            ->will($this->returnValue(new Role(User::ROLE_DEFAULT)));

        $this->userManager->updateUser($user);

        $this->assertEquals(self::TEST_EMAIL, $user->getEmail());
    }

    public function testFindUserBy()
    {
        $crit = ['id' => 0];

        $this->repository
            ->expects($this->once())
            ->method('findOneBy')
            ->with($this->equalTo($crit))
            ->will($this->returnValue([]));

        $this->userManager->findUserBy($crit);
    }

    public function testFindUsers()
    {
        $this->repository
            ->expects($this->once())
            ->method('findAll')
            ->will($this->returnValue([]));

        $this->userManager->findUsers();
    }

    public function testFindUserByUsername()
    {
        $crit = ['username' => self::TEST_NAME];

        $this->repository
            ->expects($this->once())
            ->method('findOneBy')
            ->with($this->equalTo($crit))
            ->will($this->returnValue([]));

        $this->userManager->findUserByUsernameOrEmail(self::TEST_NAME);
    }

    public function testFindUserByEmail()
    {
        $crit = ['email' => self::TEST_EMAIL];

        $this->repository
            ->expects($this->once())
            ->method('findOneBy')
            ->with($this->equalTo($crit))
            ->will($this->returnValue([]));

        $this->userManager->findUserByUsernameOrEmail(self::TEST_EMAIL);
    }

    public function testFindUserByToken()
    {
        $crit = ['confirmationToken' => self::TEST_NAME];

        $this->repository
            ->expects($this->once())
            ->method('findOneBy')
            ->with($this->equalTo($crit))
            ->will($this->returnValue([]));

        $this->userManager->findUserByConfirmationToken(self::TEST_NAME);
    }

    public function testReloadUser()
    {
        $user = $this->getUser();

        $this->om
            ->expects($this->once())
            ->method('refresh')
            ->with($this->equalTo($user));

        $this->userManager->reloadUser($user);
    }

    public function testRefreshUser()
    {
        $user = $this->getUser();
        $crit = ['id' => $user->getId()];

        $this->repository
            ->expects($this->once())
            ->method('findOneBy')
            ->with($this->equalTo($crit))
            ->will($this->returnValue([]));

        $this->userManager->refreshUser($user);
    }

    /**
     * @expectedException Symfony\Component\Security\Core\Exception\UsernameNotFoundException
     */
    public function testLoadUserByUsername()
    {
        $crit = ['username' => self::TEST_NAME];

        $this->repository
            ->expects($this->once())
            ->method('findOneBy')
            ->with($this->equalTo($crit))
            ->will($this->returnValue([]));

        $this->userManager->loadUserByUsername(self::TEST_NAME);
    }

    protected function getUser()
    {
        return $this->userFactory->create();
    }
}
