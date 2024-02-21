<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Marketplace;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface WebMarketplaceApiInterface
{
    /**
     * @return array{
     *     total: int,
     *     offset: int,
     *     limit: int,
     *     items: array<array{
     *          id: string,
     *          name: string,
     *          logo: string,
     *          author: string,
     *          partner?: string,
     *          description: string,
     *          url: string,
     *          categories: array<string>,
     *          certified?: bool,
     *     }>
     * }
     */
    public function getExtensions(int $offset = 0, int $limit = 10): array;

    /**
     * @return array{
     *     total: int,
     *     offset: int,
     *     limit: int,
     *     items: array<array{
     *          id: string,
     *          name: string,
     *          logo: string,
     *          author: string,
     *          partner?: string,
     *          description: string,
     *          url: string,
     *          categories: array<string>,
     *          certified?: bool,
     *          activate_url: string,
     *          callback_url: string,
     *     }>
     * }
     */
    public function getApps(int $offset = 0, int $limit = 10): array;

    /**
     * @return null | array{
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
     * }
     */
    public function getApp(string $id): ?array;

    public function validateCodeChallenge(string $appId, string $codeIdentifier, string $codeChallenge): bool;
}
