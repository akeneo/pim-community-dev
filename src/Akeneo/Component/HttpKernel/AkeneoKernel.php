<?php

namespace Akeneo\Component\HttpKernel;

use Symfony\Component\HttpKernel\Kernel;

abstract class AkeneoKernel extends Kernel
{
    /**
     * @inheritdoc
     */
    protected function initializeBundles()
    {
        // init bundles
        $this->bundles = [];
        $topMostBundles = [];
        $directChildren = [];

        foreach ($this->registerBundles() as $bundle) {
            $name = $bundle->getName();
            if (isset($this->bundles[$name])) {
                throw new \LogicException(sprintf('Trying to register two bundles with the same name "%s"', $name));
            }
            $this->bundles[$name] = $bundle;

            if ($parentName = $bundle->getParent()) {
                $directChildren = $this->resolveChildren($name, $parentName, $directChildren);
            } else {
                $topMostBundles[$name] = $bundle;
            }
        }

        // look for orphans
        if (!empty($directChildren) && count($diff = array_diff_key($directChildren, $this->bundles))) {
            $diff = array_keys($diff);

            throw new \LogicException(sprintf('Bundle "%s" extends bundle "%s", which is not registered.',
                $directChildren[$diff[0]], $diff[0]));
        }

        // inheritance
        $this->bundleMap = [];
        foreach ($topMostBundles as $name => $bundle) {
            $bundleMap = [$bundle];
            $hierarchy = [$name];

            while (isset($directChildren[$name])) {
                $name = $directChildren[$name];
                array_unshift($bundleMap, $this->bundles[$name]);
                $hierarchy[] = $name;
            }

            foreach ($hierarchy as $bundle) {
                $this->bundleMap[$bundle] = $bundleMap;
                array_pop($bundleMap);
            }
        }
    }

    /**
     * @param string $name
     * @param string $parentName
     * @param array  $directChildren
     *
     * @return array
     */
    public function resolveChildren($name, $parentName, $directChildren)
    {
        if ($parentName == $name) {
            throw new \LogicException(sprintf('Bundle "%s" can not extend itself.', $name));
        }
        if (isset($directChildren[$parentName])) {
            $newParent = $directChildren[$parentName];
            $directChildren = $this->resolveChildren($name, $newParent, $directChildren);
        } else {
            $directChildren[$parentName] = $name;
        }

        return $directChildren;
    }
}
