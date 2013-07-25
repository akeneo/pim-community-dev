<?php

namespace Oro\Bundle\EntityConfigBundle\Cache;

use Oro\Bundle\EntityConfigBundle\Config\ConfigInterface;
use Oro\Bundle\EntityConfigBundle\Config\EntityConfig;

class FileCache implements CacheInterface
{
    private $dir;

    public function __construct($dir)
    {
        if (!is_dir($dir)) {
            throw new \InvalidArgumentException(sprintf('The directory "%s" does not exist.', $dir));
        }
        if (!is_writable($dir)) {
            throw new \InvalidArgumentException(sprintf('The directory "%s" is not writable.', $dir));
        }

        $this->dir = rtrim($dir, '\\/');
    }

    /**
     * @param $configId
     * @return EntityConfig
     */
    public function loadConfigFromCache($configId)
    {
        $path = $this->dir . '/' . $configId . '.cache.php';
        if (!file_exists($path)) {
            return null;
        }

        return include $path;
    }

    /**
     * @param                 $configId
     * @param ConfigInterface $config
     */
    public function putConfigInCache($configId, ConfigInterface $config)
    {
        $path = $this->dir . '/' . $configId . '.cache.php';
        file_put_contents($path, '<?php return unserialize(' . var_export(serialize($config), true) . ');');
    }

    /**
     * @param $configId
     */
    public function removeConfigFromCache($configId)
    {
        $path = $this->dir . '/' . $configId . '.cache.php';
        if (file_exists($path)) {
            unlink($path);
        }
    }
}
