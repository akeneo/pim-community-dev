<?php

namespace Oro\Bundle\EntityConfigBundle\Cache;

use Oro\Bundle\EntityConfigBundle\Config\ConfigInterface;
use Oro\Bundle\EntityConfigBundle\Config\Id\IdInterface;

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
     * @param IdInterface $configId
     * @return ConfigInterface
     */
    public function loadConfigFromCache(IdInterface $configId)
    {
        $path = $this->dir . '/' . $configId->getId() . '.cache.php';
        if (!file_exists($path)) {
            return null;
        }

        return include $path;
    }

    /**
     * @param ConfigInterface $config
     */
    public function putConfigInCache(ConfigInterface $config)
    {
        $path = $this->dir . '/' . $config->getConfigId()->getId() . '.cache.php';
        file_put_contents($path, '<?php return unserialize(' . var_export(serialize($config), true) . ');');
    }

    /**
     * @param IdInterface $configId
     */
    public function removeConfigFromCache(IdInterface $configId)
    {
        $path = $this->dir . '/' . $configId->getId() . '.cache.php';
        if (file_exists($path)) {
            unlink($path);
        }
    }
}
