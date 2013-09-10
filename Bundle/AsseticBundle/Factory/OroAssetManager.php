<?php

namespace Oro\Bundle\AsseticBundle\Factory;

use Symfony\Bundle\AsseticBundle\Factory\Resource\FileResource;

use Assetic\Factory\Resource\IteratorResourceInterface;
use Assetic\Asset\AssetInterface;
use Assetic\Factory\LazyAssetManager;

use Doctrine\Common\Cache\CacheProvider;

use Oro\Bundle\AsseticBundle\Node\OroAsseticNode;

class OroAssetManager
{
    /**
     * @var CacheProvider
     */
    protected $cache;

    /**
     * @var LazyAssetManager
     */
    public $am;

    /**
     * @var \Twig_Environment
     */
    protected $twig;

    /**
     * @var array
     */
    protected $assetGroups;

    /**
     * @var array
     */
    protected $compiledGroups;

    /**
     * @var OroAsseticNode[]
     */
    protected $assets;

    /**
     * Constructor
     *
     * @param LazyAssetManager $am
     * @param \Twig_Environment $twig
     * @param array $assetGroups
     * @param array $compiledGroups
     */
    public function __construct(LazyAssetManager $am, \Twig_Environment $twig, $assetGroups, $compiledGroups)
    {
        $this->am = $am;
        $this->twig = $twig;
        $this->assetGroups = $assetGroups;
        $this->compiledGroups = $compiledGroups;
    }

    /**
     * Set cache instance
     *
     * @param \Doctrine\Common\Cache\CacheProvider $cache
     */
    public function setCache(CacheProvider $cache)
    {
        $this->cache = $cache;
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
     * @return OroAsseticNode[]
     */
    public function getAssets()
    {
        $this->load();

        return $this->assets;
    }

    /**
     * @param $name
     * @return mixed
     */
    public function get($name)
    {
        $this->load();

        return $this->assets[$name]->getUnCompressAsset();
    }

    /**
     * @param $name
     * @return bool
     */
    public function has($name)
    {
        $this->load();

        return isset($this->assets[$name]);
    }

    /**
     * Load assets
     */
    public function load()
    {
        if (null === $this->assets) {
            $this->assets = $this->cache ? $this->loadAssetsFromCache() : $this->loadAssets();
        }
    }

    /**
     * Load using cache
     *
     * @return OroAsseticNode[]
     */
    protected function loadAssetsFromCache()
    {
        $cacheKey = 'assets';
        $assets = $this->cache->fetch($cacheKey);
        if (false === $assets) {
            $assets = $this->loadAssets();
            $this->cache->save($cacheKey, serialize($assets));
        } else {
            $assets = unserialize($assets);
        }
        return $assets;
    }

    /**
     * Analyze resources and collect nodes of OroAsseticNode
     *
     * @return OroAsseticNode[]
     */
    protected function loadAssets()
    {
        $result = array();

        foreach ($this->am->getResources() as $resources) {
            if (!$resources instanceof IteratorResourceInterface) {
                $resources = array($resources);
            }

            /**@var $resource FileResource */
            foreach ($resources as $resource) {
                $tokens = $this->twig->tokenize($resource->getContent(), (string)$resource);
                $nodes = $this->twig->parse($tokens);
                $result += $this->loadNode($nodes);
            }
        }

        return $result;
    }

    /**
     * @param AssetInterface $asset
     * @return int|mixed
     */
    public function getLastModified(AssetInterface $asset)
    {
        return $this->am->getLastModified($asset);
    }

    /**
     * @param $name
     * @return bool
     */
    public function hasFormula($name)
    {
        return true;
    }

    /**
     * @param $name
     * @return array
     */
    public function getFormula($name)
    {
        $this->load();

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
