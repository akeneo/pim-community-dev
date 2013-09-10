<?php
namespace Oro\Bundle\OrganizationBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;

use Oro\Bundle\OrganizationBundle\Entity\Organization;

class LoadOrganizationData extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $defaultOrganization = new Organization();

        $defaultOrganization
            ->setName('default')
            ->setCurrency('USD')
            ->setPrecision('000 000.00');

        $this->addReference('default_organization', $defaultOrganization);

        $manager->persist($defaultOrganization);
        $manager->flush();
    }

    public function getOrder()
    {
        return 1;
    }
}
