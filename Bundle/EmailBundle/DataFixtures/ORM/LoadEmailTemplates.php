<?php

namespace Oro\Bundle\EmailBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\EmailBundle\Entity\EmailTemplate;
use Symfony\Component\Finder\Finder;

class LoadEmailTemplates extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $emailTemplates = $this->getEmailTemplatesList();

        foreach ($emailTemplates as $fileName => $file) {
            $template = file_get_contents($file);
            $emailTemplate = new EmailTemplate($fileName, $template);
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
            $fileName = str_replace(array('.html.twig', '.html', '.txt.twig', '.txt'), '', $file->getFilename());
            if (preg_match('#/([\w]+Bundle)/#', $file->getPath(), $match)) {
                $fileName = $match[1] . ':' . $fileName;
            }
            $templates[$fileName] = $file->getPath() . DIRECTORY_SEPARATOR . $file->getFilename();
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
