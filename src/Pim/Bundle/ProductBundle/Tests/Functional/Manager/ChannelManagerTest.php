<?php

namespace Pim\Bundle\ProductBundle\Tests\Functional\Manager;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Test related class
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ChannelManagerTest extends WebTestCase
{
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container;

    /**
     * @var Pim\Bundle\ProductBundle\Manager\ChannelManager
     */
    protected $channelManager;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        if (null !== static::$kernel) {
            static::$kernel->shutdown();
        }

        static::$kernel = static::createKernel(array("debug" => true));
        static::$kernel->boot();

        $this->container = static::$kernel->getContainer();

        $this->channelManager = $this->container->get('pim_product.manager.channel');
    }

    /**
     * Test related method
     */
    public function testGetChannels()
    {
        $channels = $this->channelManager->getChannels();
        $expectedChannels = array('ecommerce', 'mobile');

        $this->assertCount(2, $channels);
        foreach ($channels as $channel) {
            $this->assertContains($channel->getCode(), $expectedChannels);
        }
    }

    /**
     * Test related method
     */
    public function testGetChannelChoices()
    {
        $channelChoices = $this->channelManager->getChannelChoices();
        $expectedChannelChoices = array('ecommerce' => 'E-Commerce', 'mobile' => 'Mobile');

        $this->assertCount(2, $channelChoices);
        foreach ($channelChoices as $code => $name) {
            $this->assertContains($name, $expectedChannelChoices);
            $this->assertArrayHasKey($code, $expectedChannelChoices);
        }
    }
}
