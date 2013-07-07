<?php

namespace Oro\Bundle\AsseticBundle\Factory;

use Assetic\Factory\Resource\IteratorResourceInterface;
use Assetic\Asset\AssetInterface;

use Oro\Bundle\AsseticBundle\Node\OroAsseticNode;

/**
 * A lazy asset manager is a composition of a factory and many formula loaders.
 *
 * @author Kris Wallsmith <kris.wallsmith@gmail.com>
 */
class OroAssetManager
{
    public $am;

    private $assets;
    private $loaded;
    private $loading;
    private $twig;

    public function __construct($am, $twig)
    {
        $this->loaded = false;
        $this->loading = false;
        $this->am = $am;
        $this->assets = array();
        $this->twig = $twig;
    }

    public function getAssets()
    {
        if (!$this->loaded) {
            $this->load();
        }

        return $this->assets;
    }

    public function get($name)
    {
        if (!$this->loaded) {
            $this->load();
        }

        return $this->assets[$name]->getUnCompressAsset();
    }

    public function has($name)
    {
        if (!$this->loaded) {
            $this->load();
        }

        return isset($this->assets[$name]);
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

        $assets = array();
        foreach ($this->am->getResources() as $resources) {

            if (!$resources instanceof IteratorResourceInterface) {
                $resources = array($resources);
            }

            foreach ($resources as $resource) {
                /**@var $resource \Symfony\Bundle\AsseticBundle\Factory\Resource\FileResource */
                $tokens = $this->twig->tokenize($resource->getContent(), (string) $resource);
                $nodes  = $this->twig->parse($tokens);
                $assets += $this->loadNode($nodes);
            }
        }

        $this->assets = $assets;
        $this->loaded = true;
        $this->loading = false;

        return $this->assets;
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

    public function hasFormula($name)
    {
        if (!$this->loaded) {
            $this->load();
        }

        return true;
    }

    public function getFormula($name)
    {
        if (!$this->loaded) {
            $this->load();
        }

        return array($this->assets[$name]->getAttribute('inputs'));
    }

    /**
     * Loads assets from the supplied node.
     *
     * @param \Twig_Node $node
     *
     * @return array An array of asset formulae indexed by name
     */
    private function loadNode(\Twig_Node $node)
    {
        $assets = array();
        if ($node instanceof OroAsseticNode) {
            $assets[$node->getNameUnCompress()] = $node;
        }

        foreach ($node as $child) {
            if ($child instanceof \Twig_Node) {
                $assets += $this->loadNode($child);
            }
        }

        return $assets;
    }
}
