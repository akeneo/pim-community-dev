<?php

namespace Oro\Bundle\RequireJSBundle\Provider;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Yaml\Yaml;
use Doctrine\Common\Cache\CacheProvider;

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
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
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
        return sprintf('require(%s);', json_encode($config));
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

        $include = array_keys($paths);
        foreach ($config['config']['paths'] as $key => $path) {
            // If path references a template, load it via the require text plugin
            $include[] = substr($path, -5) === '.html' ? sprintf('text!%s', $key) : $key;
        }

        $config['build']['include'] = $include;

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
                $requirejs = Yaml::parse(file_get_contents(realpath($file)));
                $config = array_replace_recursive($config, $requirejs);
            }
        }
        return $config;
    }
}
