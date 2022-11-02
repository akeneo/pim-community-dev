<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Application\Service;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface DispatchInvalidCatalogDisabledEventInterface
{
    public function __invoke(string $catalogId): void;
}
