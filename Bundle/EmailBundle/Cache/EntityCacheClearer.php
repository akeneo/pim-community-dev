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
     * Constructor.
     *
     * @param string $entityCacheDir
     * @param string $entityCacheNamespace
     */
    public function __construct($entityCacheDir, $entityCacheNamespace)
    {
        $this->entityCacheDir = $entityCacheDir;
        $this->entityCacheNamespace = $entityCacheNamespace;
    }

    /**
     * {inheritdoc}
     */
    public function clear($cacheDir)
    {
        $entityCacheDir = sprintf('%s/%s', $this->entityCacheDir, str_replace('\\', '/', $this->entityCacheNamespace));
        $fs = new Filesystem();

        $this->clearEmailAddressCache($entityCacheDir, $fs);
    }

    /**
     * Clear a proxy class for EmailAddress entity and save it in cache
     *
     * @param string $entityCacheDir
     * @param Filesystem $fs
     */
    protected function clearEmailAddressCache($entityCacheDir, Filesystem $fs)
    {
        $fs->remove(sprintf('%s/%s.php', $entityCacheDir, 'EmailAddressProxy'));
    }
}
