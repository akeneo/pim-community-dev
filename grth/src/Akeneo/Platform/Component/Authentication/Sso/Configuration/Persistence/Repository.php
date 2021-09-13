<?php

declare(strict_types=1);

namespace Akeneo\Platform\Component\Authentication\Sso\Configuration\Persistence;

use Akeneo\Platform\Component\Authentication\Sso\Configuration\Configuration;

/**
 * Repository contract to store and retrieve configuration.
 *
 * @author Yohan Blain <yohan.blain@akeneo.com>
 */
interface Repository
{
    /**
     * Persists a configuration object.
     *
     * @param Configuration $configurationRoot
     */
    public function save(Configuration $configurationRoot): void;

    /**
     * Finds a persisted configuration object.
     *
     * @param string $code
     *
     * @throws ConfigurationNotFound When no configuration object has been found for the given code.
     *
     * @return Configuration
     */
    public function find(string $code): Configuration;
}
