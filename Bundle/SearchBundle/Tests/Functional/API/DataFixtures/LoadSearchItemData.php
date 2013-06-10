<?php

namespace Oro\Bundle\SearchBundle\Tests\Functional\API\DataFixtures;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

/**
* Load customers
*
* Execute with "php app/console doctrine:fixtures:load"
*
*
*/
class LoadSearchItemData extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
{

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * Flexible entity manager
     * @var FlexibleManager
     */
    protected $manager;

    /**
     * {@inheritDoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
        $this->manager = $this->container->get('search_item_manager');
    }

    /**
     * Get product manager
     * @return SimpleManager
     */
    protected function getItemManager()
    {
        return $this->manager;
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        //$this->loadAttributes();
        $this->loadItems();
    }

    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 4;
    }

    /**
     * Load items
     * @return array
     */
    public function loadItems()
    {
        for ($ind= 1; $ind < 10; $ind++) {
            //create item
            $customer = $this->getItemManager()->createFlexible();
            //string value
            $customer->stringValue = 'item' . $ind . '@mail.com';
            $customer->integerValue = $ind*1000;
            //decimal
            $customer->decimalValue = $ind / 10.0 ;
            //float
            $customer->floatValue = $ind / 10.0 + 10;
            //boolean
            $customer->booleanValue = rand(0, 1) == true;
            //blob
            $customer->blobValue = "blob-{$ind}";
            //array
            $customer->arrayValue = array($ind);
            //datetime
            $date = new \DateTime('now', new \DateTimeZone('UTC'));
            $date->add(new \DateInterval("P{$ind}Y"));
            $customer->datetimeValue = $date;
            //guid
            $customer->guidValue = uniqid();
            //object
            $customer->objectValue = new \stdClass();

            $this->getItemManager()->getStorageManager()->persist($customer);
        }

        $this->getItemManager()->getStorageManager()->flush();

    }
}
