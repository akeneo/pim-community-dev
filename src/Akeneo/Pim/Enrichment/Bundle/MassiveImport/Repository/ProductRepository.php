<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\MassiveImport\Repository;

use Akeneo\Pim\Enrichment\Bundle\MassiveImport\Product\Product;

/**
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ProductRepository
{
    public function persist(Product $product);

    public function get(int $identifier): Product;
}
