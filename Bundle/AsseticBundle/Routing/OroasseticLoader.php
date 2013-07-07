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
use Symfony\Bundle\AsseticBundle\Config\AsseticResource;
use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

use Oro\Bundle\AsseticBundle\Factory\OroAssetManager;

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

    public function __construct(OroAssetManager $am)
    {
        $this->am = $am;

    }

    public function load($routingResource, $type = null)
    {
        $routes = new RouteCollection();

        // resources
        foreach ($this->am->am->getResources() as $resources) {
            if (!$resources instanceof \Traversable) {
                $resources = array($resources);
            }
            foreach ($resources as $resource) {
                $routes->addResource(new AsseticResource($resource));
            }
        }

        // routes
        foreach ($this->am->getAssets() as $name => $assetNode) {
            $asset = $assetNode->getUnCompressAsset();

            // add a route for each "leaf" in debug mode

                $i = 0;
                foreach ($asset as $leaf) {
                    $this->loadRouteForAsset($routes, $leaf, $name, $i++);
                }

        }

        return $routes;
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
            '_controller' => 'oro_assetic.controller:render',
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
        return 'oro_assetic' == $type;
    }
}
