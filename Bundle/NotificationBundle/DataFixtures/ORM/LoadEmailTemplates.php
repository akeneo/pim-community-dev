<?php

namespace Oro\Bundle\NotificationBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\NotificationBundle\Entity\EmailTemplate;
use Symfony\Component\Finder\Finder;

class LoadEmailTemplates extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $emailTemplates = $this->getEmailTemplatesList();

        foreach ($emailTemplates as $file) {
            $template = file_get_contents($file);
            $emailTemplate = new EmailTemplate($template);
            $manager->persist($emailTemplate);
        }

        $manager->flush();
    }

    public function getEmailTemplatesList()
    {
        $finder = new Finder();
        $dir = __DIR__ . DIRECTORY_SEPARATOR . '../data/emails';

        if (is_dir($dir)) {
            $files = $finder->files()->in($dir);
        } else {
            $files = array();
        }

        $templates = array();
        /** @var \Symfony\Component\Finder\SplFileInfo $file  */
        foreach ($files as $file) {
            $templates[] = $dir . DIRECTORY_SEPARATOR . $file->getFilename();
        }

        return $templates;
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 120;
    }
}