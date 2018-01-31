<?php

namespace Oro\Bundle\TranslationBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class OroTranslationDumpCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('oro:translation:dump')
            ->setDescription('Dumps oro js-translations')
            ->addArgument(
                'locale',
                InputArgument::OPTIONAL,
                'List of locales, whose translations should to be dumped'
            )
            ->addOption(
                'debug',
                null,
                InputOption::VALUE_OPTIONAL,
                'Flag to dump js-translation resources with debug mode',
                false
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $locales = $input->getArgument('locale');
        $locales = null === $locales ? [$this->getContainer()->getParameter('kernel.default_locale')] : explode(', ', $locales);

        $domains = $this->getContainer()->getParameter('oro_translation.js_translation.domains');
        $targetPattern = realpath($this->getContainer()->getParameter('kernel.root_dir') . '/../web')
            . $this->getContainer()->get('router')->getRouteCollection()
                ->get('oro_translation_jstranslation')->getPath();

        foreach ($locales as $locale) {
            $target = strtr($targetPattern, ['{_locale}' => $locale]);

            $output->writeln(
                sprintf(
                    '<comment>%s</comment> <info>[file+]</info> %s',
                    date('H:i:s'),
                    basename($target)
                )
            );

            $content = $this->getContainer()->get('oro_translation.controller')
                ->renderJsTranslationContent($domains, $locale, $input->getOption('debug'));

            $this->getContainer()->get('filesystem')->mkdir(dirname($target), 0777);

            if (false === @file_put_contents($target, $content)) {
                throw new \RuntimeException('Unable to write file ' . $target);
            }
        }
    }
}
