<?php
/**
 * Created by JetBrains PhpStorm.
 * User: yurio
 * Date: 04.07.13
 * Time: 19:25
 * To change this template use File | Settings | File Templates.
 */

namespace Oro\Bundle\AsseticBundle\Routing;

use Assetic\Asset\AssetInterface;
use Assetic\Factory\LazyAssetManager;
use Symfony\Bundle\AsseticBundle\Config\AsseticResource;
use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Assetic\Factory\Resource\IteratorResourceInterface;

use Oro\Bundle\AsseticBundle\Node\OroAsseticNode;

use Assetic\Cache\ConfigCache;

/**
 * Loads routes for all assets.
 *
 * Assets should only be served through the routing system for ease-of-use
 * during development.
 *
 * For example, add the following to your application's routing_dev.yml:
 *
 *     _assetic:
 *         resource: .
 *         type:     assetic
 *
 * In a production environment you should use the `assetic:dump` command to
 * create static asset files.
 *
 * @author Kris Wallsmith <kris@symfony.com>
 */
class OroasseticLoader extends Loader
{
    protected $am;
    /**
     * @var \Twig_Environment
     */
    protected $twig;

    protected $configCache;

    public function __construct(LazyAssetManager $am, $twig, ConfigCache $configCache)
    {
        $this->am = $am;
        $this->twig = $twig;
        $this->configCache = $configCache;
    }

    public function load($routingResource, $type = null)
    {
        $routes = new RouteCollection();

        // resources
        foreach ($this->am->getResources() as $resources) {
            if (!$resources instanceof \Traversable) {
                $resources = array($resources);
            }
            foreach ($resources as $resource) {
                $routes->addResource(new AsseticResource($resource));
            }
        }



        foreach ($this->am->getResources() as $resources) {

            if (!$resources instanceof IteratorResourceInterface) {
                $resources = array($resources);
            }

            $formulae = array();

            foreach ($resources as $resource) {
                $id = (string) $resource;
                if (!$this->configCache->has($id) || (!$resource->isFresh($this->configCache->getTimestamp($id)))) {

                    $tokens = $this->twig->tokenize($resource->getContent(), (string) $resource);
                    $nodes  = $this->twig->parse($tokens);

                    $formulae += $this->loadNode($nodes);
                    $this->configCache->set($id, $formulae);
                } else {
                    $formulae += $this->configCache->get($id);
                }
            }


        }

       die;


/*
        // resources
        foreach ($this->am->getResources() as $resources) {
            if (!$resources instanceof \Traversable) {
                $resources = array($resources);
            }
            foreach ($resources as $resource) {
                $routes->addResource(new AsseticResource($resource));
            }
        }

        // routes

        var_dump($this->am->getResources());die;
        foreach ($this->am->getNames() as $name) {
            $asset = $this->am->get($name);
            $formula = $this->am->getFormula($name);

            $this->loadRouteForAsset($routes, $asset, $name);

            $debug = isset($formula[2]['debug']) ? $formula[2]['debug'] : $this->am->isDebug();
            $combine = isset($formula[2]['combine']) ? $formula[2]['combine'] : !$debug;

            // add a route for each "leaf" in debug mode
            if (!$combine) {
                $i = 0;
                foreach ($asset as $leaf) {
                    $this->loadRouteForAsset($routes, $leaf, $name, $i++);
                }
            }
        }*/

        return $routes;
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
        $formulae = array();
        if ($node instanceof OroAsseticNode) {
            $inputs = $node->getAttribute('inputs');

            $formulae[$node->getAttribute('name')] = array(
                $inputs['uncompress'][0],
                $node->getAttribute('filters'),
                array(
                    'output'  => $node->getAttribute('compressAsset')->getTargetPath(),
                    'name'    => $node->getAttribute('name'),
                    'debug'   => $node->getAttribute('debug'),
                    'combine' => false,
                    'vars'    => $node->getAttribute('vars'),
                ),
            );
        }

        foreach ($node as $child) {
            if ($child instanceof \Twig_Node) {
                $formulae += $this->loadNode($child);
            }
        }

        return $formulae;
    }

    /**
     * Loads a route to serve an supplied asset.
     *
     * The fake front controller that {@link UseControllerWorker} adds to the
     * target URL will be removed before set as a route pattern.
     *
     * @param RouteCollection $routes The route collection
     * @param AssetInterface  $asset  The asset
     * @param string          $name   The name to use
     * @param integer         $pos    The leaf index
     */
    private function loadRouteForAsset(RouteCollection $routes, AssetInterface $asset, $name, $pos = null)
    {
        $defaults = array(
            '_controller' => 'assetic.controller:render',
            'name'        => $name,
            'pos'         => $pos,
        );

        // remove the fake front controller
        $pattern = str_replace('_controller/', '', $asset->getTargetPath());

        if ($format = pathinfo($pattern, PATHINFO_EXTENSION)) {
            $defaults['_format'] = $format;
        }

        $route = '_assetic_'.$name;
        if (null !== $pos) {
            $route .= '_'.$pos;
        }

        $routes->add($route, new Route($pattern, $defaults));
    }

    public function supports($resource, $type = null)
    {
        return 'oroassetic' == $type;
    }
}
