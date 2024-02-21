<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Integration\Mock;

use Akeneo\Connectivity\Connection\Infrastructure\Marketplace\WebMarketplaceApiInterface;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FakeWebMarketplaceApi implements WebMarketplaceApiInterface
{
    private array $extensions = [];
    private array $apps = [];
    private bool $codeChallengeResult = true;

    /**
     * @param array<array{
     *      id: string,
     *      name: string,
     *      logo: string,
     *      author: string,
     *      partner?: string,
     *      description: string,
     *      url: string,
     *      categories: array<string>,
     *      certified?: bool,
     * }> $extensions
     */
    public function setExtensions(array $extensions): void
    {
        $this->extensions = $extensions;
    }

    public function getExtensions(int $offset = 0, int $limit = 10): array
    {
        return [
            'total' => \count($this->extensions),
            'offset' => $offset,
            'limit' => $limit,
            'items' => \array_slice($this->extensions, $offset, $limit),
        ];
    }

    /**
     * @param array<array{
     *      id: string,
     *      name: string,
     *      logo: string,
     *      author: string,
     *      partner?: string,
     *      description: string,
     *      url: string,
     *      categories: array<string>,
     *      certified?: bool,
     *      activate_url: string,
     *      callback_url: string,
     * }> $extensions
     */
    public function setApps(array $apps): void
    {
        $this->apps = $apps;
    }

    public function getApps(int $offset = 0, int $limit = 10): array
    {
        return [
            'total' => \count($this->apps),
            'offset' => $offset,
            'limit' => $limit,
            'items' => \array_slice($this->apps, $offset, $limit),
        ];
    }

    public function getApp(string $id): ?array
    {
        return \array_filter($this->apps, fn (array $app): bool => $app['id'] === $id)[0] ?? null;
    }

    public function validateCodeChallenge(string $appId, string $codeIdentifier, string $codeChallenge): bool
    {
        return $this->codeChallengeResult;
    }

    public function setCodeChallengeResult(bool $valid): void
    {
        $this->codeChallengeResult = $valid;
    }
}
