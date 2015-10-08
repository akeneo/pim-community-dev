<?php

namespace Oro\Bundle\ConfigBundle\Tests\Unit\Config;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Doctrine\Common\Persistence\ObjectRepository;

use Oro\Bundle\ConfigBundle\Config\UserConfigManager;
use Pim\Bundle\UserBundle\Entity\User;

class UserConfigManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var UserConfigManager
     */
    protected $object;

    /**
     * @var ObjectRepository
     */
    protected $repository;

    /**
     * @var TokenStorageInterface
     */
    protected $tokenStorage;

    /**
     * @var array
     */
    protected $settings = array(
        'oro_user' => array(
            'level'    => array(
                'value' => 20,
                'type'  => 'scalar',
            )
        )
    );

    protected function setUp()
    {
        $this->om = $this->getMock('Doctrine\Common\Persistence\ObjectManager');
        $this->object = new UserConfigManager($this->om, $this->settings);

        $this->tokenStorage = $this->getMock('Symfony\Component\Security\Core\TokenStorageInterface');
        $this->group1       = $this->getMock('Oro\Bundle\UserBundle\Entity\Group');
        $this->group2       = $this->getMock('Oro\Bundle\UserBundle\Entity\Group');

        $token = $this->getMock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');
        $user  = new User();

        $this->tokenStorage
            ->expects($this->any())
            ->method('getToken')
            ->will($this->returnValue($token));

        $this->group1
            ->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(2));

        $this->group2
            ->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(3));

        $token
            ->expects($this->any())
            ->method('getUser')
            ->will($this->returnValue($user));

        $user
            ->setId(1)
            ->addGroup($this->group1)
            ->addGroup($this->group2);

        $this->object = $this->getMock(
            'Oro\Bundle\ConfigBundle\Config\UserConfigManager',
            array('loadStoredSettings'),
            array($this->om, $this->settings)
        );
    }

    public function testSecurity()
    {
        $object      = $this->object;
        $object->expects($this->exactly(3))
            ->method('loadStoredSettings');

        $object->setSecurity($this->tokenStorage);

        $this->assertEquals('user', $object->getScopedEntityName());
        $this->assertEquals(0, $object->getScopeId());
    }
}
