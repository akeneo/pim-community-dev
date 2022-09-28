<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Application\Service;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface DisableOnlyInvalidCatalogInterface
{
    /**
     * Disable the catalog if it is invalid
     *
     * @param string $catalogId
     *
     * @return bool Is the catalog has been disabled or not
     */
    public function disable(string $catalogId): bool;
}
