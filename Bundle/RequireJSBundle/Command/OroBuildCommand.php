<?php

namespace Oro\Bundle\RequireJSBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class OroBuildCommand extends ContainerAwareCommand
{
    const MAIN_CONFIG_FILE_NAME = 'js/require-config.js';
    const BUILD_CONFIG_FILE_NAME = 'build.js';
    const OPTIMIZER_FILE_PATH = 'bundles/ororequirejs/lib/r.js';

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('oro:requirejs:build')
            ->setDescription('Build single optimized js resource');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $configCommand = $this->getApplication()->find('oro:requirejs:generate-config');
        $configCommand->run(new ArrayInput(['command' => 'oro:requirejs:generate-config']), $output);

        $webRoot = $this->getContainer()->getParameter('oro_require_js.web_root');
        $config = $this->getContainer()->getParameter('oro_require_js');
        $configProvider = $this->getContainer()->get('oro_requirejs_config_provider');

        $output->writeln('Generating require.js build config');
        $buildConfigContent = $configProvider->generateBuildConfig(self::MAIN_CONFIG_FILE_NAME);
        $buildConfigContent = '(' . json_encode($buildConfigContent) . ')';
        $buildConfigFilePath = $webRoot . DIRECTORY_SEPARATOR . self::BUILD_CONFIG_FILE_NAME;
        if (false === @file_put_contents($buildConfigFilePath, $buildConfigContent)) {
            throw new \RuntimeException('Unable to write file ' . $buildConfigFilePath);
        }

        if (isset($config['js_engine']) && $config['js_engine']) {
            $output->writeln('Running code optimizer');
            $command = $config['js_engine'] . ' ' .
                self::OPTIMIZER_FILE_PATH . ' -o ' . basename($buildConfigFilePath) . ' 1>&2';
            $process = new Process($command, $webRoot);
            $process->setTimeout($config['building_timeout']);
            $process->run();
            if (!$process->isSuccessful()) {
                throw new \RuntimeException($process->getErrorOutput());
            }

            $output->writeln('Cleaning up');
            if (false === @unlink($buildConfigFilePath)) {
                throw new \RuntimeException('Unable to remove file ' . $buildConfigFilePath);
            }

            $output->writeln(
                sprintf(
                    '<comment>%s</comment> <info>[file+]</info> %s',
                    date('H:i:s'),
                    realpath($webRoot . DIRECTORY_SEPARATOR . $config['build_path'])
                )
            );
        }
    }
}
