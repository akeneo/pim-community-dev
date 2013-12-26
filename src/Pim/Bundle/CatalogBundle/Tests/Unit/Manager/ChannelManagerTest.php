<?php

namespace Pim\Bundle\CatalogBundle\Tests\Unit\Manager;

use Pim\Bundle\CatalogBundle\Entity\Channel;
use Pim\Bundle\CatalogBundle\Manager\ChannelManager;

/**
 * Test related class
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ChannelManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ChannelManager
     */
    protected $manager;

    /**
     * @var Channel
     */
    protected $channel1;

    /**
     * @var Channel
     */
    protected $channel2;

    /**
     * @staticvar string
     */
    const NOT_EXISTING_SCOPE = 'SCOPE';

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->initializeChannels();

        $this->manager = $this->createChannelManager();
    }

    /**
     * Create a channel manager
     * @param string $userScope
     *
     * @return ChannelManager
     */
    protected function createChannelManager($userScope = 'ecommerce')
    {
        $objectManager = $this->getObjectManagerMock();
        $securityContext = $this->getSecurityContextMock($userScope);

        return new ChannelManager($objectManager, $securityContext);
    }

    /**
     * Initialize the channels used in the manager
     */
    protected function initializeChannels()
    {
        $this->channel1 = $this->createChannel('ecommerce');
        $this->channel2 = $this->createChannel('mobile');
    }

    /**
     * Create a channel
     * @param string $code
     *
     * @return \Pim\Bundle\CatalogBundle\Entity\Channel
     */
    protected function createChannel($code)
    {
        $channel = new Channel();
        $channel->setCode($code);
        $channel->setLabel(ucfirst($code));

        return $channel;
    }

    /**
     * Get object manager mock
     * @return \Doctrine\Common\Persistence\ObjectManager
     */
    protected function getObjectManagerMock()
    {
        $objectManager = $this
            ->getMockBuilder('Doctrine\Common\Persistence\ObjectManager')
            ->disableOriginalConstructor()
            ->getMock();

        $repository = $this->getRepositoryMock();

        $objectManager
            ->expects($this->any())
            ->method('getRepository')
            ->will($this->returnValue($repository));

        return $objectManager;
    }

    /**
     * Get repository mock
     * @return \Doctrine\Common\Persistence\ObjectRepository
     */
    protected function getRepositoryMock()
    {
        $repository = $this
            ->getMockBuilder('Doctrine\Common\Persistence\ObjectRepository')
            ->disableOriginalConstructor()
            ->getMock();

        $repository
            ->expects($this->any())
            ->method('findBy')
            ->will($this->returnValue(array($this->channel1, $this->channel2)));

        return $repository;
    }

    /**
     * Get security context mock
     * @param string $scope
     *
     * @return \Symfony\Component\Security\Core\SecurityContext
     */
    protected function getSecurityContextMock($scope)
    {
        $securityContext = $this
            ->getMockBuilder('Symfony\Component\Security\Core\SecurityContext')
            ->disableOriginalConstructor()
            ->getMock();

        $token = $this->getTokenMock($scope);

        $securityContext
            ->expects($this->any())
            ->method('getToken')
            ->will($this->returnValue($token));

        return $securityContext;
    }

    /**
     * Get token mock
     * @param string $scope
     *
     * @return \Symfony\Component\Security\Core\Authentication\Token\TokenInterface
     */
    protected function getTokenMock($scope)
    {
        $token = $this
            ->getMockBuilder('Symfony\Component\Security\Core\Authentication\Token\TokenInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $user = $this->getUserMock($scope);

        $token
            ->expects($this->any())
            ->method('getUser')
            ->will($this->returnValue($user));

        return $token;
    }

    /**
     * Get user mock
     * @param string $scope
     *
     * @return \Oro\Bundle\UserBundle\Entity\User
     */
    protected function getUserMock($scope)
    {
        $user = $this
            ->getMock('Oro\Bundle\UserBundle\Entity\User', array('getCatalogScope'));

        $user
            ->expects($this->any())
            ->method('getCatalogScope')
            ->will(
                $this->returnValue(
                    $this->createChannel($scope)
                )
            );

        return $user;
    }

    /**
     * Test related method
     */
    public function testGetChannels()
    {
        $channels = $this->manager->getChannels();

        $this->assertCount(2, $channels);
        $this->assertEquals($this->channel1, reset($channels));
        $this->assertEquals($this->channel2, end($channels));
    }

    /**
     * Test related method
     */
    public function testGetChannelChoices()
    {
        $expectedArray = array(
            'ecommerce' => 'Ecommerce',
            'mobile'    => 'Mobile'
        );

        $channelChoices = $this->manager->getChannelChoices();
        $this->assertEquals($expectedArray, $channelChoices);
    }

    /**
     * Test related method
     */
    public function testGetChannelChoiceWithUserChannel()
    {
        $expectedArray = array(
            'ecommerce' => 'Ecommerce',
            'mobile'    => 'Mobile'
        );

        $channelChoices = $this->manager->getChannelChoiceWithUserChannel();
        $this->assertEquals($expectedArray, $channelChoices);

        // change the user channel
        $expectedArray = array_reverse($expectedArray);
        $this->createChannelManager('mobile');

        $channelChoices = $this->manager->getChannelChoiceWithUserChannel();
        $this->assertEquals($expectedArray, $channelChoices);
    }

    /**
     * Test related method
     */
    public function testGetUserChannelCode()
    {
        $userChannelCode = $this->manager->getUserChannelCode();
        $this->assertEquals('ecommerce', $userChannelCode);
    }

    /**
     * Test getChannelChoiceWithUserChannel when the user channel don't exist
     *
     * @expectedException        Exception
     * @expectedExceptionMessage User channel code is deactivated
     */
    public function testExceptionWithUserChannelDeactivate()
    {
        $manager = $this->createChannelManager(self::NOT_EXISTING_SCOPE);
        $manager->getChannelChoiceWithUserChannel();
    }
}
