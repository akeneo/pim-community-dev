<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Storage\ORM\Connector;

use \Akeneo\Pim\Enrichment\Component\Product\Query;
use Akeneo\Pim\Enrichment\Bundle\Storage\Sql;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProduct;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;

/**
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetConnectorProductsFromWriteModel implements Query\GetConnectorProducts
{
    /** @var IdentifiableObjectRepositoryInterface */
    private $productRepository;

    /**
     * @param IdentifiableObjectRepositoryInterface $productRepository
     */
    public function __construct(IdentifiableObjectRepositoryInterface $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function fromProductIdentifiers(array $identifiers): array
    {
        $products = [];
        foreach ($identifiers as $identifier) {
            $product = $this->productRepository->findOneByIdentifier($identifier->getIdentifier());
            $products[] = ConnectorProduct::fromProductWriteModel($product);
        }

        return $products;
    }
}
