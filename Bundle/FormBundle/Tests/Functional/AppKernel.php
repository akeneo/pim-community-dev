<?php

namespace Oro\Bundle\FormBundle\Tests\Functional;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel;

class AppKernel extends Kernel
{
    private $config;

    public function __construct($config)
    {
        $this->rootDir = __DIR__ . DIRECTORY_SEPARATOR ;

        parent::__construct('test', true);

        $fs = new Filesystem();
        if (!$fs->isAbsolutePath($config)) {
            $config = $this->getRootDir() . '/config/' . $config;
        }

        if (!file_exists($config)) {
            throw new \RuntimeException(sprintf('The config file "%s" does not exist.', $config));
        }

        $this->config = $config;
    }

    public function registerBundles()
    {
        return array(
            new \Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new \Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),
            new \Symfony\Bundle\TwigBundle\TwigBundle(),
            new \Symfony\Bundle\MonologBundle\MonologBundle(),
            new \Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
            new \Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle(),
            new \Oro\Bundle\MeasureBundle\OroMeasureBundle(),
            new \Oro\Bundle\FlexibleEntityBundle\OroFlexibleEntityBundle(),
            new \Oro\Bundle\FormBundle\OroFormBundle(),
            new \Oro\Bundle\FormBundle\Tests\Functional\TestBundle\TestBundle()
        );
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load($this->config);
    }

    public function getCacheDir()
    {
        return sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'OroFormBundle' . DIRECTORY_SEPARATOR . 'cache';
    }

    public function getLogDir()
    {
        return sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'OroFormBundle' . DIRECTORY_SEPARATOR . 'logs';
    }

    public function serialize()
    {
        return serialize(array($this->config));
    }

    public function unserialize($str)
    {
        call_user_func_array(array($this, '__construct'), unserialize($str));
    }
}
