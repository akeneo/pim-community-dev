<?php

namespace Oro\Bundle\RequireJSBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateConfigCommand extends ContainerAwareCommand
{
    const MAIN_CONFIG_FILE_NAME = 'js/require-config.js';

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('oro:requirejs:generate-config')
            ->setDescription('Generate requirejs configuration file');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $webRoot = $this->getContainer()->getParameter('oro_require_js.web_root');
        $config = $this->getContainer()->getParameter('oro_require_js');

        $configProvider = $this->getContainer()->get('oro_requirejs_config_provider');

        $output->writeln('Generating require.js main config');
        $mainConfigContent = $configProvider->generateMainConfig();
        // for some reason built application gets broken with configuration in "oneline-json"
        $mainConfigContent = str_replace(',', ",\n", $mainConfigContent);
        $mainConfigFilePath = $webRoot . DIRECTORY_SEPARATOR . self::MAIN_CONFIG_FILE_NAME;
        if (false === @file_put_contents($mainConfigFilePath, $mainConfigContent)) {
            throw new \RuntimeException('Unable to write file ' . $mainConfigFilePath);
        }
    }
}
