<?php
namespace Oro\Bundle\AsseticBundle\Routing;

use Assetic\Asset\AssetInterface;
use Symfony\Bundle\AsseticBundle\Config\AsseticResource;
use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

use Oro\Bundle\AsseticBundle\Factory\OroAssetManager;

class AsseticLoader extends Loader
{
    /**
     * @var OroAssetManager
     */
    protected $am;

    /**
     * @param OroAssetManager $am
     */
    public function __construct(OroAssetManager $am)
    {
        $this->am = $am;
    }

    /**
     * @param mixed $routingResource The resource
     * @param string $type           The resource type
     * @return RouteCollection
     */
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

            $i = 0;
            foreach ($asset as $leaf) {
                $this->loadRouteForAsset($routes, $leaf, $name, $i++);
            }

        }

        return $routes;
    }

    public function supports($resource, $type = null)
    {
        return 'oro_assetic' == $type;
    }

    /**
     * Loads a route to serve an supplied asset.
     *
     * The fake front controller that {@link UseControllerWorker} adds to the
     * target URL will be removed before set as a route pattern.
     *
     * @param RouteCollection $routes The route collection
     * @param AssetInterface $asset  The asset
     * @param string $name   The name to use
     * @param integer $pos    The leaf index
     */
    private function loadRouteForAsset(RouteCollection $routes, AssetInterface $asset, $name, $pos = null)
    {
        $defaults = array(
            '_controller' => 'oro_assetic.controller:render',
            'name' => $name,
            'pos' => $pos,
        );

        // remove the fake front controller
        $pattern = str_replace('_controller/', '', $asset->getTargetPath());

        if ($format = pathinfo($pattern, PATHINFO_EXTENSION)) {
            $defaults['_format'] = $format;
        }

        $route = '_assetic_' . $name;
        if (null !== $pos) {
            $route .= '_' . $pos;
        }

        $routes->add($route, new Route($pattern, $defaults));
    }
}
