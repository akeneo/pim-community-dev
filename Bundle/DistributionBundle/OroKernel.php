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

            // require instead of require_once used to correctly handle sub-requests
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
            ->in(
                array(
                    $this->getRootDir() . '/../src',
                    $this->getRootDir() . '/../vendor',
                )
            )
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
                        'name'     => $class,
                        'kernel'   => $kernel,
                        'priority' => $priority,
                    );
                }
            }
        }

        uasort($bundles, array($this, 'compareBundles'));

        return $bundles;
    }

    public function compareBundles($a, $b)
    {
        // @todo: this is preliminary algorithm. we need to implement more sophisticated one,
        // for example using bundle dependency info from composer.json
        $p1 = (int) $a['priority'];
        $p2 = (int) $b['priority'];

        if ($p1 == $p2) {
            $n1 = (string) $a['name'];
            $n2 = (string) $b['name'];

            // make sure OroCRM bundles follow Oro bundles
            if (strpos($n1, 'Oro') === 0 && strpos($n2, 'Oro') === 0) {
                if (strpos($n1, 'OroCRM') === 0) {
                    return 1;
                }
                if (strpos($n2, 'OroCRM') === 0) {
                    return -1;
                }
            }

            // bundles with the same priorities are sorted alphabetically
            return strcasecmp($n1, $n2);
        }

        // sort be priority
        return ($p1 < $p2) ? -1 : 1;
    }
}
