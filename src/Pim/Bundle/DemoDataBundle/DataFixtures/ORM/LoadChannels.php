<?php
namespace Pim\Bundle\CatalogBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Pim\Bundle\CatalogTaxinomyBundle\Entity\Channel;
use Pim\Bundle\CatalogTaxinomyBundle\Entity\ChannelLocale;

/**
 * Load channels
 *
 * Execute with "php app/console doctrine:fixtures:load"
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class LoadChannels extends AbstractFixture implements OrderedFixtureInterface
{

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        // add channels
        $channel = new Channel();
        $channel->setCode('E-commerce Magento');
        $manager->persist($channel);

        $channel = new Channel();
        $channel->setCode('Mobile catalog');
        $manager->persist($channel);

        // persist
        $manager->persist($channel);
        $manager->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 1;
    }

}
