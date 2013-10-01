<?php
namespace Oro\Bundle\UserBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;

use Oro\Bundle\UserBundle\Entity\Group;

class LoadGroupData extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        /**
         * addRole was commented due a ticket BAP-1675
         */
        $defaultBusinessUnit = null;
        if ($this->hasReference('default_business_unit')) {
            $defaultBusinessUnit = $this->getReference('default_business_unit');
        }
        $administrators = new Group('Administrators');
        //$administrators->addRole($this->getReference('administrator_role'));
        if ($defaultBusinessUnit) {
            $administrators->setOwner($defaultBusinessUnit);
        }
        $manager->persist($administrators);
        $this->setReference('oro_group_administrators', $administrators);

        $sales= new Group('Sales');
        //$sales->addRole($this->getReference('manager_role'));
        if ($defaultBusinessUnit) {
            $sales->setOwner($defaultBusinessUnit);
        }
        $manager->persist($sales);
        $this->setReference('oro_group_sales', $sales);

        $marketing= new Group('Marketing');
        //$marketing->addRole($this->getReference('manager_role'));
        if ($defaultBusinessUnit) {
            $marketing->setOwner($defaultBusinessUnit);
        }
        $manager->persist($marketing);
        $this->setReference('oro_group_marketing', $marketing);

        $manager->flush();
    }

    public function getOrder()
    {
        return 100;
    }
}
