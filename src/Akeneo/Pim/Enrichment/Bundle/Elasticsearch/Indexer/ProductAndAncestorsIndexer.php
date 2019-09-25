<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Indexer;

use Akeneo\Pim\Enrichment\Bundle\Product\Query\Sql\GetAncestorProductModelCodes;
use Akeneo\Pim\Enrichment\Component\Product\Storage\Indexer\ProductIndexerInterface;
use Akeneo\Pim\Enrichment\Component\Product\Storage\Indexer\ProductModelIndexerInterface;

/**
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductAndAncestorsIndexer
{
    /** @var ProductIndexerInterface */
    private $productIndexer;

    /** @var ProductModelIndexerInterface */
    private $productModelIndexer;

    /** @var GetAncestorProductModelCodes */
    private $getAncestorProductModelCodes;

    public function __construct(
        ProductIndexerInterface $productIndexer,
        ProductModelIndexerInterface $productModelIndexer,
        GetAncestorProductModelCodes $getAncestorProductModelCodes
    ) {
        $this->productIndexer = $productIndexer;
        $this->productModelIndexer = $productModelIndexer;
        $this->getAncestorProductModelCodes = $getAncestorProductModelCodes;
    }

    public function indexFromProductIdentifiers(array $identifiers, array $options = []): void
    {
        $ancestorProductModelCodes = $this->getAncestorProductModelCodes->fromProductIdentifiers($identifiers);
        if (!empty($ancestorProductModelCodes)) {
            $this->productModelIndexer->indexFromProductModelCodes($ancestorProductModelCodes, $options);
        }
        $this->productIndexer->indexFromProductIdentifiers($identifiers, $options);
    }
}
