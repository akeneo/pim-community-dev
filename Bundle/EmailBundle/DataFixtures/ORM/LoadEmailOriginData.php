<?php

namespace Oro\Bundle\EmailBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\EmailBundle\Entity\EmailOrigin;

class LoadEmailOriginData extends AbstractFixture
{
    /**
     * Load email origins
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $emailOrigin = new EmailOrigin();
        $emailOrigin->setName('BAP');

        $manager->persist($emailOrigin);
        $manager->flush();
    }
}
