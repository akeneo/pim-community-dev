<?php

namespace Oro\Bundle\NavigationBundle\Title;

use Oro\Bundle\NavigationBundle\Provider\TitleServiceInterface;
use Symfony\Component\Routing\Router;
use Symfony\Component\Translation\MessageCatalogue;
use Symfony\Component\Translation\Extractor\ExtractorInterface;

class TranslationExtractor implements ExtractorInterface
{
    /**
     * @var \Oro\Bundle\NavigationBundle\Provider\TitleService
     */
    private $titleService;

    /**
     * @var \Symfony\Component\Routing\Router
     */
    private $router;

    /**
     * @var string
     */
    private $prefix;

    /**
     * @param \Oro\Bundle\NavigationBundle\Provider\TitleServiceInterface $titleService
     * @param \Symfony\Component\Routing\Router $router
     */
    public function __construct(TitleServiceInterface $titleService, Router $router)
    {
        $this->titleService = $titleService;
        $this->router = $router;
    }

    /**
     * Extract titles for translation
     *
     * @param string                                          $directory
     * @param \Symfony\Component\Translation\MessageCatalogue $catalogue
     *
     * @return MessageCatalogue
     */
    public function extract($directory, MessageCatalogue $catalogue)
    {
        $routes = $this->getRoutesByBundleDir($directory);

        $titles = $this->titleService->getStoredTitlesRepository()->getTitles($routes);

        foreach ($titles as $titleRecord) {
            $message = $titleRecord['title'];
            $catalogue->set($message, $this->prefix . $message);
        }

        return $catalogue;
    }

    /**
     * Get routes by bundle dir
     *
     * @param string $dir
     * @return array|\Symfony\Component\Routing\Route
     */
    public function getRoutesByBundleDir($dir)
    {
        $routes = $this->router->getRouteCollection()->all();

        $resultRoutes = array();
        /** @var \Symfony\Component\Routing\Route $route */
        foreach ($routes as $name => $route) {
            if ($this->getBundleNameFromString($dir) ==
                $this->getBundleNameFromString($route->getDefault('_controller'))
            ) {
                $resultRoutes[] = $name;
            }
        }

        return $resultRoutes;
    }

    public function getBundleNameFromString($string)
    {
        $bundleName = false;
        if (preg_match('#[/|\\\]([\w]+Bundle)[/|\\\]#', $string, $match)) {
            $bundleName = $match[1];
        }

        return $bundleName;
    }

    /**
     * Set prefix for translated strings
     *
     * @param string $prefix
     */
    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;
    }
}
