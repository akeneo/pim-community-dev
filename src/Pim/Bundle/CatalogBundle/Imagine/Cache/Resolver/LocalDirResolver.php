<?php

namespace Pim\Bundle\CatalogBundle\Imagine\Cache\Resolver;

use Symfony\Component\Finder\Finder;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Liip\ImagineBundle\Imagine\Cache\Resolver\AbstractFilesystemResolver;
use Liip\ImagineBundle\Imagine\Cache\Resolver\WebPathResolver;
use Symfony\Component\HttpFoundation\Response;

class LocalDirResolver extends WebPathResolver
{
    /** @var string */
    protected $rootDir;

    public function __construct(Filesystem $filesystem, $rootDir)
    {
        parent::__construct($filesystem);

        $this->rootDir = $rootDir;
    }

    /**
     * {@inheritDoc}
     */
    public function resolve(Request $request, $path, $filter)
    {
        $targetPath = $this->getFilePath($path, $filter);
        if (file_exists($targetPath)) {
            return new Response(file_get_contents($targetPath), 200);
        }

        return $targetPath;
    }

    /**
     * {@inheritDoc}
     */
    public function getBrowserPath($targetPath, $filter, $absolute = false)
    {
        throw new \Exception(
            'The Pim\Bundle\CatalogBundle\Imagine\Cache\Resolver\LocalDirResolver is not meant to generate browser path'
        );
    }

    /**
     * {@inheritDoc}
     */
    public function clear($cachePrefix)
    {
        // Let's just avoid to remove the web/ directory content if cache prefix is empty
        if ($cachePrefix === '') {
            throw new \InvalidArgumentException(
                'Cannot clear the Imagine cache because the cache_prefix is empty in your config.'
            );
        }

        $cachePath = $this->rootDir . '/' . $cachePrefix;

        // Avoid an exception if the cache path does not exist (i.e. Imagine didn't yet render any image)
        if (is_dir($cachePath)) {
            $this->filesystem->remove(Finder::create()->in($cachePath)->depth(0)->files());
        }
    }

    /**
     * {@inheritDoc}
     */
    protected function getFilePath($path, $filter)
    {
        return $this->rootDir . '/' . $filter . '/' . $path;
    }
}
