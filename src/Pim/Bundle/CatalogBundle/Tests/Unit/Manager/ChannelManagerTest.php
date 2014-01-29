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
     *
     * @return ChannelManager
     */
    protected function createChannelManager()
    {
        $objectManager = $this->getObjectManagerMock();

        return new ChannelManager($objectManager);
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
}
