<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Domain\CustomApps\Persistence;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface UpdateCustomAppSecretQueryInterface
{
    public function execute(
        string $clientId,
        string $clientSecret,
    ): void;
}
