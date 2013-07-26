<?php

namespace Oro\Bundle\EmailBundle\DataFixtures\ORM;

use Symfony\Component\Finder\Finder;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;

use Oro\Bundle\EmailBundle\Entity\EmailTemplate;

abstract class AbstractEmailFixture extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $emailTemplates = $this->getEmailTemplatesList($this->getEmailsDir());

        foreach ($emailTemplates as $fileName => $file) {
            $template = file_get_contents($file['path']);
            $emailTemplate = new EmailTemplate($fileName, $template, $file['format']);
            $manager->persist($emailTemplate);
        }

        $manager->flush();
    }

    /**
     * @param string $dir
     * @return array
     */
    public function getEmailTemplatesList($dir)
    {
        if (is_dir($dir)) {
            $finder = new Finder();
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

            $format = 'html';
            if (preg_match('#\.(html|txt)(\.twig)?#', $file->getFilename(), $match)) {
                $format = $match[1];
            }

            $templates[$fileName] = array(
                'path'   => $file->getPath() . DIRECTORY_SEPARATOR . $file->getFilename(),
                'format' => $format,
            );
        }

        return $templates;
    }

    /**
     * Return path to email templates
     *
     * @return string
     */
    abstract public function getEmailsDir();

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 120;
    }
}
