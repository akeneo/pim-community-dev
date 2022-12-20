<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Application\Persistence\Catalog\Product;

use Akeneo\Catalogs\Application\Exception\InvalidProductSelectionCriteriaException;
use Akeneo\Catalogs\Domain\Catalog;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @phpstan-import-type ProductSelectionCriteria from Catalog
 */
interface CountProductsSelectedByCriteriaQueryInterface
{
    /**
     * @param ProductSelectionCriteria $productSelectionCriteria
     *
     * @throws InvalidProductSelectionCriteriaException
     */
    public function execute(array $productSelectionCriteria): int;
}
