<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Query;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\GetElasticsearchProductModelProjectionInterface;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Model\ElasticsearchProductModelProjection;
use Akeneo\Pim\Enrichment\Bundle\Product\Query\Sql\GetAttributesFromProductModelCodes;
use Akeneo\Pim\Enrichment\Bundle\Product\Query\Sql\GetCompleteFilterFromProductModelCodes;
use Akeneo\Pim\Enrichment\Bundle\Product\Query\Sql\GetValuesAndPropertiesFromProductModelCodes;
use Akeneo\Pim\Enrichment\Component\Product\Factory\Read\ValueCollectionFactory;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\ProductAndProductModel\ProductModelNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetElasticsearchProductModelProjection implements GetElasticsearchProductModelProjectionInterface
{
    /** @var GetValuesAndPropertiesFromProductModelCodes */
    private $getValuesAndPropertiesFromProductModelCodes;

    /** @var GetCompleteFilterFromProductModelCodes */
    private $getCompleteFilterFromProductModelCodes;

    /** @var GetAttributesFromProductModelCodes */
    private $getAttributesFromProductModelCodes;

    /** @var ValueCollectionFactory */
    private $valueCollectionFactory;

    /** @var NormalizerInterface */
    private $valueCollectionNormalizer;

    public function __construct(
        GetValuesAndPropertiesFromProductModelCodes $getValuesAndPropertiesFromProductModelCodes,
        GetCompleteFilterFromProductModelCodes $getCompleteFilterFromProductModelCodes,
        GetAttributesFromProductModelCodes $getAttributesFromProductModelCodes,
        ValueCollectionFactory $valueCollectionFactory,
        NormalizerInterface $valueCollectionNormalizer
    ) {
        $this->getValuesAndPropertiesFromProductModelCodes = $getValuesAndPropertiesFromProductModelCodes;
        $this->getCompleteFilterFromProductModelCodes = $getCompleteFilterFromProductModelCodes;
        $this->getAttributesFromProductModelCodes = $getAttributesFromProductModelCodes;
        $this->valueCollectionFactory = $valueCollectionFactory;
        $this->valueCollectionNormalizer = $valueCollectionNormalizer;
    }

    public function fromProductModelCodes(array $productModelCodes): array
    {
        $valuesAndProperties = $this
            ->getValuesAndPropertiesFromProductModelCodes
            ->fetchByProductModelCodes($productModelCodes);
        $completeFilters = $this
            ->getCompleteFilterFromProductModelCodes
            ->fetchByProductModelCodes($productModelCodes);
        $attributes = $this
            ->getAttributesFromProductModelCodes
            ->fetchByProductModelCodes($productModelCodes);

        $productProjections = [];

        foreach ($productModelCodes as $productModelCode) {
            $valueCollection = $this
                ->valueCollectionFactory
                ->createFromStorageFormat($valuesAndProperties[$productModelCode]['values']);
            $values = $this
                ->valueCollectionNormalizer
                ->normalize($valueCollection, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX);

            $productProjections[$productModelCode] = new ElasticsearchProductModelProjection(
                $valuesAndProperties[$productModelCode]['id'],
                $valuesAndProperties[$productModelCode]['code'],
                $valuesAndProperties[$productModelCode]['created'],
                $valuesAndProperties[$productModelCode]['updated'],
                $valuesAndProperties[$productModelCode]['family_code'],
                $valuesAndProperties[$productModelCode]['family_labels'],
                $valuesAndProperties[$productModelCode]['family_variant_code'],
                $valuesAndProperties[$productModelCode]['category_codes'],
                $valuesAndProperties[$productModelCode]['ancestor_category_codes'],
                $valuesAndProperties[$productModelCode]['parent_code'],
                $values,
                $completeFilters[$productModelCode]['all_complete'],
                $completeFilters[$productModelCode]['all_incomplete'],
                $valuesAndProperties[$productModelCode]['parent_id'],
                $valuesAndProperties[$productModelCode]['labels'],
                $attributes[$productModelCode]['ancestor_attribute_codes'],
                $attributes[$productModelCode]['attributes_for_this_level']
            );
        }

        return $productProjections;
    }
}
