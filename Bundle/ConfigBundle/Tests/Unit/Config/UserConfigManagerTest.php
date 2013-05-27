<?php

namespace Oro\Bundle\ConfigBundle\Config;

use Symfony\Component\Security\Core\SecurityContextInterface;

use Doctrine\Common\Persistence\ObjectRepository;

use Oro\Bundle\ConfigBundle\Entity\Config;
use Oro\Bundle\UserBundle\Entity\User;

class UserConfigManagerTest extends ConfigManagerTest
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
     * @var SecurityContextInterface
     */
    protected $security;

    protected function setUp()
    {
        parent::setUp();

        $this->repository = $this->getMock('Doctrine\Common\Persistence\ObjectRepository');
        $this->security   = $this->getMock('Symfony\Component\Security\Core\SecurityContextInterface');
        $this->group1     = $this->getMock('Oro\Bundle\UserBundle\Entity\Group');
        $this->group2     = $this->getMock('Oro\Bundle\UserBundle\Entity\Group');

        $token  = $this->getMock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');
        $user   = new User();

        $this->om
            ->expects($this->any())
            ->method('getRepository')
            ->withAnyParameters()
            ->will($this->returnValue($this->repository));

        $this->security
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

        $this->object = new UserConfigManager($this->om, $this->settings);
    }

    public function testSecurity()
    {
        $object      = $this->object;
        $configUser  = new Config();
        $configGroup = new Config();

        $configUser->setSettings(array(
            'oro_user' => array(
                'level' => 30,
             ),
        ));

        $configGroup->setSettings(array(
            'oro_test' => array(
                'anysetting' => 'qwerty',
             ),
        ));

        $this->repository
            ->expects($this->any())
            ->method('findOneBy')
            ->with($this->logicalOr(
                $this->equalTo(array(
                    'entity'   => 'Oro\Bundle\UserBundle\Entity\User',
                    'recordId' => 1,
                )),
                $this->equalTo(array(
                    'entity'   => get_class($this->group1),
                    'recordId' => 2
                )),
                $this->equalTo(array(
                    'entity'   => get_class($this->group2),
                    'recordId' => 3
                ))
            ))
            ->will($this->returnCallback(
                function ($param) use ($configUser, $configGroup) {
                    switch ($param['recordId']) {
                        case 1:
                            return $configUser;

                        case 2:
                            return $configGroup;

                        case 3:
                            return null;
                    }
                }
            ));

        $object->setSecurity($this->security);

        $this->assertEquals(30, $object->get('oro_user.level'));
        $this->assertEquals('qwerty', $object->get('oro_test.anysetting'));
    }
}
