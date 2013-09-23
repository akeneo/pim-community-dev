<?php

namespace Oro\Bundle\EmailBundle\DataFixtures\ORM\Email;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

use Extend\Cache\OroEmailBundle\Entity\EmailAddressProxy;
use Oro\Bundle\EmailBundle\Entity\Email;

class LoadEmails extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $fromEmail = new EmailAddressProxy();
        $fromEmail->setEmail(uniqid().'@gmail.com');

        $email = new Email();
        $email->setSubject(uniqid())
            ->setFromName(uniqid())
            ->setReceivedAt(new \DateTime())
            ->setSentAt(new \DateTime())
            ->setInternalDate(new \DateTime())
            ->setFromEmailAddress($fromEmail)
            ->setMessageId(uniqid());

        $manager->persist($fromEmail);
        $manager->persist($email);

        $manager->flush();
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 120;
    }
}
