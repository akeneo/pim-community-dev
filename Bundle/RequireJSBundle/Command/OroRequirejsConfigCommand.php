<?php

namespace Oro\Bundle\RequireJSBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Yaml\Yaml;

use Doctrine\Common\Cache\CacheProvider;

class OroRequirejsConfigCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('oro:requirejs:config')
            ->setDescription('Create require.js configuration')
            ->addArgument('write_to', InputArgument::OPTIONAL, 'path for require.js configuration');
    }

    /**
     * Get array with assets from config files
     *
     * @return array
     */
    protected function combineConfig()
    {
        $config = $this->getContainer()->getParameter('oro_require_js.config');
        $bundles = $this->getContainer()->getParameter('kernel.bundles');
        foreach ($bundles as $bundle) {
            $reflection = new \ReflectionClass($bundle);
            if (is_file($file = dirname($reflection->getFilename()) . '/Resources/config/requirejs.yml')) {
                $requirejs = Yaml::parse(realpath($file));
                if (isset($requirejs['config'])) {
                    $config = array_merge_recursive($config, $requirejs['config']);
                }
            }
        }
        if (!empty($config['paths']) && is_array($config['paths'])) {
            foreach ($config['paths'] as &$path) {
                if (substr($path, 0, 8) === 'bundles/') {
                    $path = substr($path, 8);
                }
                if (substr($path, -3) === '.js') {
                    $path = substr($path, 0, -3);
                }
            }
        }
        return $config;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $content = 'require(' . json_encode($this->combineConfig()) . ');';

        $path = $input->getArgument('write_to');
        if (empty($path)) {
            $path = $this->getContainer()->getParameter('oro_require_js.config_path');
        }

        $target = realpath($this->getContainer()->getParameter('kernel.root_dir') . '/../web/' . $path);

        $output->writeln(
            sprintf(
                '<comment>%s</comment> <info>[file+]</info> %s',
                date('H:i:s'),
                $target
            )
        );

        $this->getContainer()->get('filesystem')->mkdir(dirname($target), 0777);

        if (false === @file_put_contents($target, $content)) {
            throw new \RuntimeException('Unable to write file ' . $target);
        }
    }
}
