<?php
namespace Oro\Bundle\OrganizationBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;

use Oro\Bundle\OrganizationBundle\Entity\BusinessUnit;

class LoadBusinessUnitData extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $defaultBusinessUnit = new BusinessUnit();

        $defaultBusinessUnit
            ->setName('Root')
            ->setOrganization($this->getReference('default_organization'));

        $this->addReference('default_business_unit', $defaultBusinessUnit);

        $manager->persist($defaultBusinessUnit);
        $manager->flush();
    }

    public function getOrder()
    {
        return 2;
    }
}
