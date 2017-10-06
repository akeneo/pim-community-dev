<?php

namespace Pim\Bundle\EnrichBundle\VersionStrategy;

use Pim\Bundle\CatalogBundle\Version;
use Symfony\Component\Asset\VersionStrategy\VersionStrategyInterface;

class CacheBusterVersionStrategy implements VersionStrategyInterface
{
    public function getVersion($path)
    {
        return Version::VERSION;
    }

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
