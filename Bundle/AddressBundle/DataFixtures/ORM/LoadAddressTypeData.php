<?php

namespace Oro\Bundle\AddressBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;

use Oro\Bundle\AddressBundle\Entity\AddressType;

class LoadAddressTypeData extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * Load address types
     *
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $shippingAddressType = new AddressType();
        $shippingAddressType->setType('shipping');

        $billingAddressType = new AddressType();
        $billingAddressType->setType('billing');

        $manager->persist($shippingAddressType);
        $manager->persist($billingAddressType);

        $manager->flush();
    }

    public function getOrder()
    {
        return 50;
    }
}
