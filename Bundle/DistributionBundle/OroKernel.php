<?php

namespace Oro\Bundle\DistributionBundle;

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\ConfigCache;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Yaml;

use Oro\Bundle\DistributionBundle\Dumper\PhpBundlesDumper;

abstract class OroKernel extends Kernel
{
    /**
     * Get the list of all "autoregistered" bundles
     *
     * @return array List ob bundle objects
     */
    public function registerBundles()
    {
        $bundles = array();

        if (!$this->getCacheDir()) {
            foreach ($this->collectBundles() as $bundle) {
                $bundles[] = $bundle['kernel']
                    ? new $bundle['class']($this)
                    : new $bundle['class'];
            }
        } else {
            $file  = $this->getCacheDir() . '/bundles.php';
            $cache = new ConfigCache($file, false);

            if (!$cache->isFresh($file)) {
                $dumper = new PhpBundlesDumper($this->collectBundles());

                $cache->write($dumper->dump());
            }

            $bundles = require_once $cache;
        }

        return $bundles;
    }

    protected function collectBundles()
    {
        $finder  = new Finder();
        $bundles = array();

        $finder
            ->files()
            ->in(array(
                $this->getRootDir() . '/../src',
                $this->getRootDir() . '/../vendor',
            ))
            ->name('bundles.yml');

        foreach ($finder as $file) {
            $import = Yaml::parse($file->getRealpath());

            foreach ($import['bundles'] as $bundle) {
                if (is_array($bundle)) {
                    $class  = $bundle['name'];
                    $kernel = isset($bundle['kernel']) && true == $bundle['kernel'];
                } else {
                    $class  = $bundle;
                    $kernel = false;
                }

                if (!isset($bundles[$class])) {
                    $bundles[$class] = $kernel;
                }
            }
        }

        return $bundles;
    }
}
