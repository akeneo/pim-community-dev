<?php

namespace Oro\Bundle\RequireJSBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

class OroRequirejsConfigCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('oro:requirejs:config')
            ->setDescription('Create require.js configuration');
    }

    /**
     * Collects settings for main require.js config
     *
     * @return array
     */
    protected function combineConfig()
    {
        $require_js = $this->getContainer()->getParameter('oro_require_js');
        $config = $require_js['config'];
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

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Combine require.js main config');
        $content = 'require(' . json_encode($this->combineConfig()) . ');';
        // for some reason built application gets broken with configuration in "oneline-json"
        $content = str_replace(',', ",\n", $content);
        $require_js = $this->getContainer()->getParameter('oro_require_js');

        $target = realpath($this->getContainer()->getParameter('kernel.root_dir') . '/../web') .
            '/' . $require_js['config_path'];

        $this->getContainer()->get('filesystem')->mkdir(dirname($target), 0777);

        if (false === @file_put_contents($target, $content)) {
            throw new \RuntimeException('Unable to write file ' . $target);
        }

        $output->writeln(
            sprintf(
                '<comment>%s</comment> <info>[file+]</info> %s',
                date('H:i:s'),
                realpath($target)
            )
        );
    }
}
