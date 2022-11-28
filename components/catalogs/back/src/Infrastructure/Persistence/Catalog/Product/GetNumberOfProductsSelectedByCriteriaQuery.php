<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Persistence\Catalog\Product;

use Akeneo\Catalogs\Application\Exception\InvalidProductSelectionCriteriaException;
use Akeneo\Catalogs\Application\Persistence\Catalog\Product\GetNumberOfProductsSelectedByCriteriaQueryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetNumberOfProductsSelectedByCriteriaQuery implements GetNumberOfProductsSelectedByCriteriaQueryInterface
{
    public function __construct(
        private ProductQueryBuilderFactoryInterface $productQueryBuilderFactory,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function execute(array $productSelectionCriteria): int
    {
        $pqbOptions = [
            'filters' => GetPQBFilters::fromProductSelectionCriteria($productSelectionCriteria),
            'limit' => 0,
        ];

        try {
            $pqb = $this->productQueryBuilderFactory->create($pqbOptions);
            $results = $pqb->execute();

            return $results->count();
        } catch (\Exception $exception) {
            throw new InvalidProductSelectionCriteriaException(previous: $exception);
        }
    }
}
