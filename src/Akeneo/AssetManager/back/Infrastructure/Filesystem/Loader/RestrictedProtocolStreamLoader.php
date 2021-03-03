<?php

namespace Akeneo\AssetManager\Infrastructure\Filesystem\Loader;

use Liip\ImagineBundle\Binary\Loader\LoaderInterface;
use Liip\ImagineBundle\Exception\Binary\Loader\NotLoadableException;

class RestrictedProtocolStreamLoader implements LoaderInterface
{
    private LoaderInterface $loader;

    /** @var string[] */
    private array $allowedProtocols;

    public function __construct(LoaderInterface $loader, array $allowedProtocols)
    {
        $this->loader = $loader;
        $this->allowedProtocols = $allowedProtocols;
    }

    public function find($path)
    {
        if (!$this->pathContainAnAllowedProtocol($path)) {
            throw new NotLoadableException('Source image path does not contain an allowed protocol');
        }

        return $this->loader->find($path);
    }

    private function pathContainAnAllowedProtocol(string $url): bool
    {
        foreach ($this->allowedProtocols as $allowedProtocol) {
            if (str_starts_with($url, $allowedProtocol)) {
                return true;
            }
        }

        return false;
    }
}
