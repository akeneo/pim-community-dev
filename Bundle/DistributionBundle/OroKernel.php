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
            foreach ($this->collectBundles() as $class => $params) {
                $bundles[] = $params['kernel']
                    ? new $class($this)
                    : new $class;
            }
        } else {
            $file  = $this->getCacheDir() . '/bundles.php';
            $cache = new ConfigCache($file, false);

            if (!$cache->isFresh($file)) {
                $dumper = new PhpBundlesDumper($this->collectBundles());

                $cache->write($dumper->dump());
            }

            $bundles = require $cache;
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
                $kernel   = false;
                $priority = 0;

                if (is_array($bundle)) {
                    $class    = $bundle['name'];
                    $kernel   = isset($bundle['kernel']) && true == $bundle['kernel'];
                    $priority = isset($bundle['priority']) ? (int) $bundle['priority'] : 0;
                } else {
                    $class    = $bundle;
                }

                if (!isset($bundles[$class])) {
                    $bundles[$class] = array(
                        'kernel'   => $kernel,
                        'priority' => $priority,
                    );
                }
            }
        }

        uasort($bundles, function ($a, $b) {
            $p1 = (int) $a['priority'];
            $p2 = (int) $b['priority'];

            if ($p1 == $p2) {
                return 0;
            }

            return ($p1 < $p2) ? -1 : 1;
        });

        return $bundles;
    }
}
