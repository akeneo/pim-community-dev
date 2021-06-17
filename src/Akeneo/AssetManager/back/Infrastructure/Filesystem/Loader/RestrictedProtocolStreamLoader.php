<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Infrastructure\Filesystem\Loader;

use Liip\ImagineBundle\Binary\Loader\LoaderInterface;
use Liip\ImagineBundle\Exception\Binary\Loader\NotLoadableException;
use Webmozart\Assert\Assert;

final class RestrictedProtocolStreamLoader implements LoaderInterface
{
    private LoaderInterface $loader;
    /** @var string[] */
    private array $allowedProtocols;

    public function __construct(LoaderInterface $loader, array $allowedProtocols)
    {
        Assert::allString($allowedProtocols);
        $this->loader = $loader;
        $this->allowedProtocols = $allowedProtocols;
    }

    /**
     * {@inheritDoc}
     */
    public function find($path)
    {
        $this->checkPathContainsAnAllowedProtocol($path);

        return $this->loader->find($path);
    }

    private function checkPathContainsAnAllowedProtocol(string $url): void
    {
        $urlParts = \explode('://', $url);
        if (count($urlParts) < 2) {
            return; // Relative urls are authorized.
        }

        if (!\in_array(\strtolower($urlParts[0]), $this->allowedProtocols)) {
            throw new NotLoadableException('Source image path does not contain an allowed protocol');
        }
    }
}
