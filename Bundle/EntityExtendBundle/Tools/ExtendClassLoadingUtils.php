<?php

namespace Oro\Bundle\EntityExtendBundle\Tools;

use Symfony\Component\ClassLoader\UniversalClassLoader;
use Symfony\Component\Yaml\Yaml;

class ExtendClassLoadingUtils
{
    /**
     * Returns base cache directory where all data for extended entities should be located.
     *
     * @param string $cacheDir
     * @return string
     */
    public static function getEntityBaseCacheDir($cacheDir)
    {
        return $cacheDir . '/oro_entities/Extend';
    }

    /**
     * Returns directory where extended entities should be located.
     *
     * @param string $cacheDir
     * @return string
     */
    public static function getEntityCacheDir($cacheDir)
    {
        return $cacheDir . '/oro_entities/Extend/Entity';
    }

    /**
     * Returns a path of a configuration file contains class aliases for extended entities.
     *
     * @param string $cacheDir
     * @return string
     */
    public static function getAliasesPath($cacheDir)
    {
        return self::getEntityCacheDir($cacheDir) . '/alias.yml';
    }

    /**
     * Registers the extended entity namespace in the autoloader.
     *
     * @param string $cacheDir
     */
    public static function registerClassLoader($cacheDir)
    {
        $loader = new UniversalClassLoader();
        $loader->registerNamespaces(
            array('Extend\\' => $cacheDir . '/oro_entities')
        );
        $loader->register();
    }

    /**
     * Sets class aliases for extended entities.
     *
     * @param string $cacheDir
     */
    public static function setAliases($cacheDir)
    {
        $aliasesPath = self::getAliasesPath($cacheDir);
        if (file_exists($aliasesPath)) {
            $aliases = Yaml::parse(
                file_get_contents($aliasesPath, FILE_USE_INCLUDE_PATH)
            );

            if (is_array($aliases)) {
                foreach ($aliases as $className => $alias) {
                    if (class_exists($className) && !class_exists($alias, false)) {
                        $aliasArr   = explode('\\', $alias);
                        $shortAlias = array_pop($aliasArr);

                        class_alias($className, $shortAlias);
                        class_alias($className, $alias);
                    }
                }
            }
        }
    }
}
