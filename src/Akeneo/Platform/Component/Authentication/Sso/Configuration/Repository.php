<?php

declare(strict_types=1);

namespace Akeneo\Platform\Component\Authentication\Sso\Configuration;

/**
 * Repository contract to store and retrieve configuration.
 *
 * @author Yohan Blain <yohan.blain@akeneo.com>
 */
interface Repository
{
    public function save(Root $configurationRoot): void;

    public function find(string $code): ?Root;
}
