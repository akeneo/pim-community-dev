<?php

namespace Oro\Bundle\AsseticBundle\Factory;

use Symfony\Bundle\AsseticBundle\Factory\Resource\FileResource;

use Assetic\Factory\Resource\IteratorResourceInterface;
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
            $this->assets = $this->loadAssets();
        }
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
