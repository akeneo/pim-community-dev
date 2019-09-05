<?php

namespace Akeneo\Platform\Bundle\UIBundle\VersionStrategy;

use Akeneo\Platform\VersionProviderInterface;
use Symfony\Component\Asset\VersionStrategy\VersionStrategyInterface;

class CacheBusterVersionStrategy implements VersionStrategyInterface
{
    /** @var VersionProviderInterface */
    protected $versionProvider;

    /**
     * @param VersionProviderInterface $versionProvider
     */
    public function __construct(VersionProviderInterface $versionProvider)
    {
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
        $versioned = sprintf('%s?%s', ltrim($path, DIRECTORY_SEPARATOR), md5($this->getVersion($path)));

        if ($path && DIRECTORY_SEPARATOR == $path[0]) {
            return DIRECTORY_SEPARATOR . $versioned;
        }

        return $versioned;
    }
}
