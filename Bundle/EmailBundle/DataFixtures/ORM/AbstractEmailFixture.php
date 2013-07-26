<?php

namespace Oro\Bundle\EmailBundle\DataFixtures\ORM;


class AbstractEmailFixture extends AbstractFixture
{
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
}
