<?php

namespace Oro\Bundle\RequireJSBundle\Provider;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Yaml\Yaml;
use Doctrine\Common\Cache\CacheProvider;
use Symfony\Component\Templating\TemplateReferenceInterface;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;

class Config
{
    const REQUIREJS_CONFIG_CACHE_KEY = 'requirejs_config';

    /**
     * @var \Doctrine\Common\Cache\CacheProvider
     */
    protected $cache;

    /**
     * @var  ContainerInterface
     */
    protected $container;

    /**
     * @var EngineInterface
     */
    protected $templating;

    /**
     * @var string|TemplateReferenceInterface
     */
    protected $template;

    /**
     * @param ContainerInterface $container
     * @param EngineInterface $templating
     * @param $template
     */
    public function __construct(ContainerInterface $container, EngineInterface $templating, $template)
    {
        $this->container = $container;
        $this->templating = $templating;
        $this->template = $template;
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
     * Fetches piece of JS-code with require.js main config from cache
     * or if it was not there - generates and put into a cache
     *
     * @return string
     */
    public function getMainConfig()
    {
        $config = null;
        if ($this->cache) {
            $config = $this->cache->fetch(self::REQUIREJS_CONFIG_CACHE_KEY);
        }
        if (empty($config)) {
            $config = $this->generateMainConfig();
            if ($this->cache) {
                $this->cache->save(self::REQUIREJS_CONFIG_CACHE_KEY, $config);
            }
        }
        return $config;
    }

    /**
     * Generates main config for require.js
     *
     * @return string
     */
    public function generateMainConfig()
    {
        $requirejs = $this->collectConfigs();
        $config = $requirejs['config'];
        if (!empty($config['paths']) && is_array($config['paths'])) {
            foreach ($config['paths'] as &$path) {
                if (substr($path, 0, 8) === 'bundles/') {
                    $path = substr($path, 8);
                }
                if (substr($path, -3) === '.js') {
                    $path = substr($path, 0, -3);
                }
            }
        }
        return $this->templating->render($this->template, array('config' => $config));
    }

    /**
     * Generates build config for require.js
     *
     * @param string $configPath path to require.js main config
     * @return array
     */
    public function generateBuildConfig($configPath)
    {
        $config = $this->collectConfigs();

        $config['build']['baseUrl'] = './bundles';
        $config['build']['out'] = './' . $config['build_path'];
        $config['build']['mainConfigFile'] = './' . $configPath;

        $paths = array(
            // build-in configuration
            'require-config' => '../' . substr($configPath, 0, -3),
            // build-in require.js lib
            'require-lib' => 'ororequirejs/lib/require',
        );

        $config['build']['paths'] = array_merge($config['build']['paths'], $paths);
        $config['build']['include'] = array_merge(
            array_keys($paths),
            array_keys($config['config']['paths'])
        );

        return $config['build'];
    }

    /**
     * Goes across bundles and collects configurations
     *
     * @return array
     */
    public function collectConfigs()
    {
        $config = $this->container->getParameter('oro_require_js');
        $bundles = $this->container->getParameter('kernel.bundles');
        foreach ($bundles as $bundle) {
            $reflection = new \ReflectionClass($bundle);
            if (is_file($file = dirname($reflection->getFilename()) . '/Resources/config/requirejs.yml')) {
                $requirejs = Yaml::parse(realpath($file));
                $config = array_merge_recursive($config, $requirejs);
            }
        }
        return $config;
    }
}
