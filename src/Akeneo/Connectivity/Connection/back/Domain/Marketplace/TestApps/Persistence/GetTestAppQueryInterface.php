<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Domain\Marketplace\TestApps\Persistence;

interface GetTestAppQueryInterface
{
    /**
     * @return array{
     *     id: string,
     *     secret: string,
     *     name: string,
     *     author: string|null,
     *     activate_url: string,
     *     callback_url: string,
     *     connected: bool,
     * }|null
     */
    public function execute(string $id): ?array;
}
