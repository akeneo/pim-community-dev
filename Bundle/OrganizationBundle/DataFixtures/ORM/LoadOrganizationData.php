<?php
namespace Oro\Bundle\OrganizationBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

use Oro\Bundle\OrganizationBundle\Entity\Organization;

class LoadOrganizationData extends AbstractFixture
{
    public function load(ObjectManager $manager)
    {
        $defaultOrganization = new Organization();

        $defaultOrganization
            ->setName('default')
            ->setCurrency('USD')
            ->setPrecision('000 000.00');

        $manager->persist($defaultOrganization);
        $manager->flush();
    }
}