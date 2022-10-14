<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Application\Storage;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface CatalogsMappingStorageInterface
{
    public function exists(string $location): bool;

    /**
     * @return resource
     */
    public function read(string $location);

    public function write(string $location, string $contents): void;

    public function delete(string $location): void;
}
