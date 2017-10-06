<?php

namespace Pim\Bundle\EnrichBundle\VersionStrategy;

use Pim\Bundle\CatalogBundle\VersionProviderInterface;
use Symfony\Component\Asset\VersionStrategy\VersionStrategyInterface;

class CacheBusterVersionStrategy implements VersionStrategyInterface
{
    /** @var VersionProviderInterface */
    protected $versionProvider;

    /**
     * @param VersionProviderInterface $versionProvider
     */
    public function __construct(VersionProviderInterface $versionProvider) {
        $this->versionProvider = $versionProvider;
    }

    /**
     * @param string $path
     */
    public function getVersion($path)
    {
        return $this->versionProvider->getPatch();
    }

    /**
     * @param string $path
     */
    public function applyVersion($path)
    {
        $version = $this->getVersion($path);

        if ('' === $version) {
            return $path;
        }

        $versioned = sprintf('%s?%s', ltrim($path, '/'), $version);

        if ($path && '/' === $path[0]) {
            return '/'.$versioned;
        }

        return $versioned;
    }
}
