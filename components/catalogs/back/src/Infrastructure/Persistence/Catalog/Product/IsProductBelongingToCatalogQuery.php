<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Persistence\Catalog\Product;

use Akeneo\Catalogs\Application\Persistence\Catalog\Product\IsProductBelongingToCatalogQueryInterface;
use Akeneo\Catalogs\Domain\Catalog;
use Akeneo\Catalogs\Infrastructure\Service\FormatProductSelectionCriteria;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\IdentifierResult;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class IsProductBelongingToCatalogQuery implements IsProductBelongingToCatalogQueryInterface
{
    public function __construct(
        private ProductQueryBuilderFactoryInterface $productQueryBuilderFactory,
    ) {
    }

    public function execute(Catalog $catalog, string $productUuid): bool
    {
        $pqb = $this->productQueryBuilderFactory->create([
            'filters' => FormatProductSelectionCriteria::toPQBFilters($catalog->getProductSelectionCriteria()),
            'limit' => 1,
        ]);
        $pqb->addFilter('id', Operators::EQUALS, $productUuid);
        $results = $pqb->execute();
        /** @var IdentifierResult $result */
        $result = $results->current();

        return $results->count() === 1 && $this->getUuidFromIdentifierResult($result->getId()) === $productUuid;
    }

    private function getUuidFromIdentifierResult(string $esId): string
    {
        $matches = [];
        if (!\preg_match(
            '/^product_(?P<uuid>[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12})$/',
            $esId,
            $matches
        )) {
            throw new \LogicException(\sprintf('Invalid Elasticsearch identifier %s', $esId));
        }

        return $matches['uuid'];
    }
}
