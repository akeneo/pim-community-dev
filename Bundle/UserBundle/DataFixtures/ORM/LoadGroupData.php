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
        $administrators = new Group('Administrators');
        $administrators->addRole($this->getReference('administrator_role'));
        $administrators->setOwner($this->getReference('default_business_unit'));
        $manager->persist($administrators);
        $this->setReference('oro_group_administrators', $administrators);

        $sales= new Group('Sales');
        $sales->addRole($this->getReference('manager_role'));
        $sales->setOwner($this->getReference('default_business_unit'));
        $manager->persist($sales);
        $this->setReference('oro_group_sales', $sales);

        $marketing= new Group('Marketing');
        $marketing->addRole($this->getReference('manager_role'));
        $marketing->setOwner($this->getReference('default_business_unit'));
        $manager->persist($marketing);
        $this->setReference('oro_group_marketing', $marketing);

        $manager->flush();
    }

    public function getOrder()
    {
        return 100;
    }
}
