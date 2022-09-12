<?php

declare(strict_types=1);

namespace Akeneo\Category\Infrastructure\FileSystem\Loader;

use Liip\ImagineBundle\Binary\Loader\LoaderInterface;
use Liip\ImagineBundle\Exception\Binary\Loader\NotLoadableException;
use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class RestrictedProtocolStreamLoader implements LoaderInterface
{
    /** @param $allowedProtocols string[] */
    public function __construct(
        private LoaderInterface $loader,
        private array $allowedProtocols
    ) {
        Assert::allString($allowedProtocols);
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
