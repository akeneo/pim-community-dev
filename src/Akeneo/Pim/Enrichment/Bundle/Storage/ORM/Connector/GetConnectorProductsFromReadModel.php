<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Storage\ORM\Connector;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\IdentifierResult;
use Akeneo\Pim\Enrichment\Bundle\Product\Query\Sql\GetCategoryCodesByProductIdentifiers;
use Akeneo\Pim\Enrichment\Bundle\Product\Query\Sql\GetProductAssociationsByProductIdentifiers;
use Akeneo\Pim\Enrichment\Bundle\Product\Query\Sql\GetValuesAndPropertiesFromProductIdentifiers;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProduct;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProductList;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueCollection;
use Akeneo\Pim\Enrichment\Component\Product\Query\GetConnectorProducts;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderInterface;

class GetConnectorProductsFromReadModel implements GetConnectorProducts
{
    /** @var GetValuesAndPropertiesFromProductIdentifiers */
    private $getValuesAndPropertiesFromProductIdentifiers;

    /** @var GetProductAssociationsByProductIdentifiers */
    private $getProductAssociationsByProductIdentifiers;

    /** @var GetCategoryCodesByProductIdentifiers */
    private $getCategoryCodesByProductIdentifiers;

    public function __construct(
        GetValuesAndPropertiesFromProductIdentifiers $getValuesAndPropertiesFromProductIdentifiers,
        GetProductAssociationsByProductIdentifiers $getProductAssociationsByProductIdentifiers,
        GetCategoryCodesByProductIdentifiers $getCategoryCodesByProductIdentifiers
    ) {
        $this->getValuesAndPropertiesFromProductIdentifiers = $getValuesAndPropertiesFromProductIdentifiers;
        $this->getProductAssociationsByProductIdentifiers = $getProductAssociationsByProductIdentifiers;
        $this->getCategoryCodesByProductIdentifiers = $getCategoryCodesByProductIdentifiers;
    }

    /**
     * {@inheritdoc}
     */
    public function fromProductQueryBuilder(
        ProductQueryBuilderInterface $pqb,
        ?array $attributesToFilterOn,
        ?string $channelToFilterOn,
        ?array $localesToFilterOn
    ): ConnectorProductList {
        $result = $pqb->execute();
        $identifiers = array_map(function (IdentifierResult $identifier) {
            return $identifier->getIdentifier();
        }, iterator_to_array($result));

        $associations = [];
        foreach ($this->getProductAssociationsByProductIdentifiers->fetchByProductIdentifiers($identifiers)
                 as $productIdentifier => $productAssociations) {
            $associations[$productIdentifier] = ['associations' => $productAssociations];
        }

        $categoryCodes = [];
        foreach ($this->getCategoryCodesByProductIdentifiers->fetchCategoryCodes($identifiers)
                 as $productIdentifier => $productCategoryCodes) {
            $categoryCodes[$productIdentifier] = ['category_codes'] = $productCategoryCodes;
        }

        $rows = array_replace_recursive(
            $this->getValuesAndPropertiesFromProductIdentifiers->fetchByProductIdentifiers($identifiers),
            $associations,
            $categoryCodes
        );

        $products = [];
        foreach ($rows as $row) {
            $products[] = new ConnectorProduct(
                $row['id'],
                $row['identifier'],
                $row['created'],
                $row['updated'],
                $row['is_enabled'],
                $row['family_code'],
                $row['category_codes'],
                $row['group_codes'],
                $row['product_model_code'],
                $row['associations'],
                [],
                new ValueCollection()//$row['raw_values']
            );
        }

        return new ConnectorProductList($result->count(), $products);
    }
}
