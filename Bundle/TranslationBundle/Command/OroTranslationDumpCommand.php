<?php

namespace Oro\Bundle\TranslationBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Oro\Bundle\TranslationBundle\Controller\TranslationController;

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
            ->addArgument('locale',
                InputArgument::OPTIONAL | InputArgument::IS_ARRAY,
                'List of locales, whose translations should to be dumped'
            )
            ->addOption('debug',
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
        if (empty($locales)) {
            $locales[] = $this->getContainer()->getParameter('kernel.default_locale');
        }

        $options = $this->getContainer()->getParameter('oro_translation.js_translation');
        $targetFormat = realpath($this->getContainer()->getParameter('kernel.root_dir') . '/../web')
            . $this->getContainer()->get('router')->getRouteCollection()
                ->get('oro_translation_bundle')->getPath();

        foreach ($locales as $locale) {
            $target = strtr($targetFormat, array('{_locale}' => $locale));

            $output->writeln(sprintf(
                '<comment>%s</comment> <info>[file+]</info> %s',
                date('H:i:s'),
                basename($target)
            ));

            $domainsTranslations = $this->getContainer()->get('translator')
                ->getTranslations($options['domains'], $locale);

            $content = array(
                'locale'         => $locale,
                'defaultDomains' => $options['domains'],
                'messages'       => array(),
            );

            foreach ($domainsTranslations as $domain => $translations) {
                $content['messages'] += array_combine(array_map(function($id) use ($domain) {
                    return sprintf('%s:%s', $domain, $id);
                }, array_keys($translations)), array_values($translations));
            }

            if ($input->getOption('debug')) {
                $content['debug'] = true;
            }

            $content = $this->getContainer()->get('templating')
                ->render(TranslationController::JS_TRANSLATION_TEMPLATE, array(
                    'json' => $content
                ));

            $this->getContainer()->get('filesystem')->mkdir(dirname($target), 0777);

            if (false === @file_put_contents($target, $content)) {
                throw new \RuntimeException('Unable to write file ' . $target);
            }
        }
    }
}
