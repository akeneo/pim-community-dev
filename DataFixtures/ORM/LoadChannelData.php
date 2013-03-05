<?php
namespace Pim\Bundle\DemoBundle\DataFixtures\ORM;

use Pim\Bundle\ConfigBundle\Entity\Channel;

use Doctrine\Common\Persistence\ObjectManager;

use Symfony\Component\DependencyInjection\ContainerInterface;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;

use Doctrine\Common\DataFixtures\OrderedFixtureInterface;

use Doctrine\Common\DataFixtures\AbstractFixture;

/**
 * Load fixtures for channels
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class LoadChannelData extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
{

    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container;

    /**
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        // create channels
        $channel = $this->createChannel('ecommerce', 'E-Commerce');
        $manager->persist($channel);

        $channel = $this->createChannel('mobile', 'Mobile');
        $manager->persist($channel);

        $channel = $this->createChannel('catalog', 'Catalog');
        $manager->persist($channel);

        $manager->flush();
    }

    /**
     * Create a channel
     * @param string $code Channel code
     * @param string $name Channel name
     *
     * @return \Pim\Bundle\ConfigBundle\Entity\Channel
     */
    protected function createChannel($code, $name)
    {
        $channel = new Channel();
        $channel->setCode($code);
        $channel->setName($name);

        $this->setReference('channel.'. $code, $channel);

        return $channel;
    }

    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 0;
    }
}
