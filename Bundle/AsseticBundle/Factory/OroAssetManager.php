<?php

namespace Oro\Bundle\AsseticBundle\Factory;

use Assetic\Asset\AssetCollectionInterface;
use Assetic\Asset\AssetInterface;
use Assetic\AssetManager;
use Assetic\Factory\Loader\FormulaLoaderInterface;
use Assetic\Factory\Resource\ResourceInterface;
use Assetic\Filter\DependencyExtractorInterface;

/**
 * A lazy asset manager is a composition of a factory and many formula loaders.
 *
 * @author Kris Wallsmith <kris.wallsmith@gmail.com>
 */
class LazyAssetManager extends AssetManager
{
    private $factory;
    private $loaders;
    private $resources;
    private $formulae;
    private $loaded;
    private $loading;

    /**
     * Constructor.
     *
     * @param AssetFactory $factory The asset factory
     * @param array        $loaders An array of loaders indexed by alias
     */
    public function __construct($am, $loader)
    {
        $this->am = $am;

            $this->setLoader($loader);

    }

    /**
     * Loads formulae from resources.
     *
     * @throws \LogicException If a resource has been added to an invalid loader
     */
    public function load()
    {
        if ($this->loading) {
            return;
        }
        $this->loading = true;

        foreach ($this->resources as $resources) {
            foreach ($resources as $resource) {
                $this->formulae = array_replace($this->formulae, $this->loaders->load($resource));
            }
        }

        $this->loaded = true;
        $this->loading = false;
    }

    public function get($name)
    {
        if (!$this->loaded) {
            $this->load();
        }

        if (!parent::has($name) && isset($this->formulae[$name])) {
            list($inputs, $filters, $options) = $this->formulae[$name];
            $options['name'] = $name;
            parent::set($name, $this->factory->createAsset($inputs, $filters, $options));
        }

        return parent::get($name);
    }

    public function has($name)
    {
        if (!$this->loaded) {
            $this->load();
        }

        return isset($this->formulae[$name]) || parent::has($name);
    }

    public function getNames()
    {
        if (!$this->loaded) {
            $this->load();
        }

        return array_unique(array_merge(parent::getNames(), array_keys($this->formulae)));
    }

    public function isDebug()
    {
        return $this->factory->isDebug();
    }

    public function getLastModified(AssetInterface $asset)
    {
        $mtime = 0;
        foreach ($asset instanceof AssetCollectionInterface ? $asset : array($asset) as $leaf) {
            $mtime = max($mtime, $leaf->getLastModified());

            if (!$filters = $leaf->getFilters()) {
                continue;
            }

            // prepare load path
            $sourceRoot = $leaf->getSourceRoot();
            $sourcePath = $leaf->getSourcePath();
            $loadPath = $sourceRoot && $sourcePath ? dirname($sourceRoot.'/'.$sourcePath) : null;

            $prevFilters = array();
            foreach ($filters as $filter) {
                $prevFilters[] = $filter;

                if (!$filter instanceof DependencyExtractorInterface) {
                    continue;
                }

                // extract children from leaf after running all preceeding filters
                $clone = clone $leaf;
                $clone->clearFilters();
                foreach (array_slice($prevFilters, 0, -1) as $prevFilter) {
                    $clone->ensureFilter($prevFilter);
                }
                $clone->load();

                foreach ($filter->getChildren($this->factory, $clone->getContent(), $loadPath) as $child) {
                    $mtime = max($mtime, $this->getLastModified($child));
                }
            }
        }

        return $mtime;
    }
}
