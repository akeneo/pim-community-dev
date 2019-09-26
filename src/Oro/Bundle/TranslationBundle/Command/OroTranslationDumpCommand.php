<?php

namespace Oro\Bundle\TranslationBundle\Command;

use Oro\Bundle\TranslationBundle\Controller\Controller;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Routing\RouterInterface;

class OroTranslationDumpCommand extends Command
{
    protected static $defaultName = 'oro:translation:dump';

    /** @var Controller */
    private $controller;

    /** @var Filesystem */
    private $filesystem;

    /** @var string */
    private $jsTranslationDomains;

    /** @var string */
    private $rootDir;

    /** @var string */
    private $defaultLocale;

    /** @var RouterInterface */
    private $router;

    public function __construct(
        Controller $controller,
        Filesystem $filesystem,
        RouterInterface $router,
        array $jsTranslationDomains,
        string $rootDir,
        string $defaultLocale
    ) {
        parent::__construct();

        $this->controller = $controller;
        $this->filesystem = $filesystem;
        $this->router = $router;
        $this->jsTranslationDomains = $jsTranslationDomains;
        $this->rootDir = $rootDir;
        $this->defaultLocale = $defaultLocale;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
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
        $locales = null === $locales ? [$this->defaultLocale] : explode(', ', $locales);

        $domains = $this->jsTranslationDomains;
        $targetPattern = realpath($this->rootDir . '/../public')
            . $this->router->getRouteCollection()
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

            $content = $this->controller
                ->renderJsTranslationContent($domains, $locale, $input->getOption('debug'));

            $this->filesystem->mkdir(dirname($target), 0777);

            if (false === @file_put_contents($target, $content)) {
                throw new \RuntimeException('Unable to write file ' . $target);
            }
        }
    }
}
