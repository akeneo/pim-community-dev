<?php

namespace Oro\Bundle\AsseticBundle\Factory;

use Assetic\Factory\Resource\IteratorResourceInterface;
use Assetic\Asset\AssetInterface;
use Assetic\Factory\LazyAssetManager;

use Oro\Bundle\AsseticBundle\Node\OroAsseticNode;

class OroAssetManager
{
    /**
     * @var LazyAssetManager
     */
    public $am;

    /**
     * @var \Twig_Environment
     */
    protected $twig;

    protected $assetGroups;
    protected $compiledGroups;

    protected $assets;
    protected $loaded;
    protected $loading;


    public function __construct(LazyAssetManager $am, \Twig_Environment $twig, $assetGroups, $compiledGroups)
    {
        $this->loaded = false;
        $this->loading = false;
        $this->am = $am;
        $this->assets = array();
        $this->twig = $twig;
        $this->assetGroups = $assetGroups;
        $this->compiledGroups = $compiledGroups;
    }

    /**
     * @return array
     */
    public function getAssetGroups()
    {
        return $this->assetGroups;
    }

    /**
     * @return array
     */
    public function getCompiledGroups()
    {
        return $this->compiledGroups;
    }

    /**
     * @return array
     */
    public function getAssets()
    {
        $this->checkLoad();

        return $this->assets;
    }

    /**
     * @param $name
     * @return mixed
     */
    public function get($name)
    {
        $this->checkLoad();

        return $this->assets[$name]->getUnCompressAsset();
    }

    /**
     * @param $name
     * @return bool
     */
    public function has($name)
    {
        $this->checkLoad();

        return isset($this->assets[$name]);
    }

    /**
     * @return array
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
                $tokens = $this->twig->tokenize($resource->getContent(), (string)$resource);
                $nodes = $this->twig->parse($tokens);
                $assets += $this->loadNode($nodes);
            }
        }

        $this->assets = $assets;
        $this->loaded = true;
        $this->loading = false;

        return $this->assets;
    }

    /**
     * @param AssetInterface $asset
     * @return int|mixed
     */
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
            $loadPath = $sourceRoot && $sourcePath ? dirname($sourceRoot . '/' . $sourcePath) : null;

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

    /**
     * @param $name
     * @return bool
     */
    public function hasFormula($name)
    {
        $this->checkLoad();

        return true;
    }

    /**
     * @param $name
     * @return array
     */
    public function getFormula($name)
    {
        $this->checkLoad();

        return array($this->assets[$name]->getAttribute('inputs'));
    }

    /**
     * Check if assets was loaded
     */
    private function checkLoad()
    {
        if (!$this->loaded) {
            $this->load();
        }
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
