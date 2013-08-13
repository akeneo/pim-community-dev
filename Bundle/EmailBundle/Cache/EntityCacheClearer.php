<?php

namespace Oro\Bundle\EmailBundle\Cache;

use Symfony\Component\HttpKernel\CacheClearer\CacheClearerInterface;

use Symfony\Component\Filesystem\Filesystem;

class EntityCacheClearer implements CacheClearerInterface
{
    /**
     * @var string
     */
    private $entityCacheDir;

    /**
     * @var string
     */
    private $entityCacheNamespace;

    /**
     * @var string
     */
    private $entityProxyNameTemplate;

    /**
     * Constructor.
     *
     * @param string $entityCacheDir
     * @param string $entityCacheNamespace
     * @param string $entityProxyNameTemplate
     */
    public function __construct($entityCacheDir, $entityCacheNamespace, $entityProxyNameTemplate)
    {
        $this->entityCacheDir = $entityCacheDir;
        $this->entityCacheNamespace = $entityCacheNamespace;
        $this->entityProxyNameTemplate = $entityProxyNameTemplate;
    }

    /**
     * {inheritdoc}
     */
    public function clear($cacheDir)
    {
        $fs = $this->createFilesystem();

        $entityCacheDir = sprintf('%s/%s', $this->entityCacheDir, str_replace('\\', '/', $this->entityCacheNamespace));

        $this->clearEmailAddressCache($entityCacheDir, $fs);
    }

    /**
     * Create Filesystem object
     *
     * @return Filesystem
     */
    protected function createFilesystem()
    {
        return new Filesystem();
    }

    /**
     * Clear a proxy class for EmailAddress entity and save it in cache
     *
     * @param string $entityCacheDir
     * @param Filesystem $fs
     */
    protected function clearEmailAddressCache($entityCacheDir, Filesystem $fs)
    {
        $className = sprintf($this->entityProxyNameTemplate, 'EmailAddress');
        $fs->remove(sprintf('%s/%s.php', $entityCacheDir, $className));
    }
}
