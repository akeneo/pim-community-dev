<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Domain\CustomApps\Persistence;

interface GetCustomAppQueryInterface
{
    /**
     * @return array{
     *     id: string,
     *     name: string,
     *     author: string|null,
     *     activate_url: string,
     *     callback_url: string,
     *     connected: bool,
     * }|null
     */
    public function execute(string $id): ?array;
}
