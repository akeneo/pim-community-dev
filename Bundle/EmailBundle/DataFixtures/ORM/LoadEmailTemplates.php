<?php

namespace Oro\Bundle\EmailBundle\DataFixtures\ORM;

use Symfony\Component\Finder\Finder;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;

use Oro\Bundle\EmailBundle\Entity\EmailTemplate;

class LoadEmailTemplates extends AbstractEmailFixture implements OrderedFixtureInterface
{
    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $emailTemplates = $this->getEmailTemplatesList(__DIR__ . DIRECTORY_SEPARATOR . '../data/emails');

        foreach ($emailTemplates as $fileName => $file) {
            $template = file_get_contents($file['path']);
            $emailTemplate = new EmailTemplate($fileName, $template, $file['format']);
            $manager->persist($emailTemplate);
        }

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
