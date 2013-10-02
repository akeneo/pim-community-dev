<?php

namespace Oro\Bundle\RequireJSBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Process\Process;

class OroRequirejsBuildCommand extends ContainerAwareCommand
{
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
     * Generates build configuration
     *
     * Collects options from main config + requirejs.yml and generates build-config
     *
     * @return array
     */
    protected function generateBuildConfig()
    {
        $config = $this->getContainer()->getParameter('oro_require_js');
        $bundles = $this->getContainer()->getParameter('kernel.bundles');
        foreach ($bundles as $bundle) {
            $reflection = new \ReflectionClass($bundle);
            if (is_file($file = dirname($reflection->getFilename()) . '/Resources/config/requirejs.yml')) {
                $requirejs = Yaml::parse(realpath($file));
                $config = array_merge_recursive($config, $requirejs);
            }
        }

        $config['build']['baseUrl'] = './bundles';
        $config['build']['out'] = './' . $config['build_path'];
        $config['build']['mainConfigFile'] = './' . $config['config_path'];

        $paths = array(
            // build-in configuration
            'require-config' => '../' . substr($config['config_path'], 0, -3),
            // build-in require.js lib
            'require-lib' => 'ororequirejs/lib/require',
        );

        $config['build']['paths'] = array_merge($config['build']['paths'], $paths);
        $config['build']['include'] = array_merge(
            array_keys($paths),
            array_keys($config['config']['paths'])
        );

        return $config['build'];
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $config = $this->getContainer()->getParameter('oro_require_js');
        $webRoot = realpath($this->getContainer()->getParameter('kernel.root_dir') . '/../web');

        $output->writeln('Generate require.js build config');
        $buildConfig = $this->generateBuildConfig();
        $mainConfigFile = $webRoot . '/' . $buildConfig['mainConfigFile'];
        if (!file_exists($mainConfigFile)) {
            throw new \RuntimeException('Main config file "' . $mainConfigFile . '" does not exist');
        }
        $contentBuildConfig = '(' . json_encode($buildConfig) . ')';
        $targetBuildConfig = $webRoot . '/build.js';
        if (false === @file_put_contents($targetBuildConfig, $contentBuildConfig)) {
            throw new \RuntimeException('Unable to write file ' . $targetBuildConfig);
        }

        $output->writeln('Run code optimization');
        $command = $config['js_engine'] . ' bundles/ororequirejs/lib/r.js -o ' . basename($targetBuildConfig);
        $process = new Process($command, $webRoot);
        $process->setTimeout($config['building_timeout']);
        $process->run();
        if (!$process->isSuccessful()) {
            throw new \RuntimeException($process->getErrorOutput());
        }

        if (false === @unlink($targetBuildConfig)) {
            throw new \RuntimeException('Unable to remove file ' . $targetBuildConfig);
        }

        $output->writeln(
            sprintf(
                '<comment>%s</comment> <info>[file+]</info> %s',
                date('H:i:s'),
                realpath($webRoot . '/' . $config['build_path'])
            )
        );
    }
}
