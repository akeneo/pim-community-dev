<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Normalizer\ExternalApi;

use Akeneo\Pim\Automation\DataQualityInsights\PublicApi\Model\QualityScoreCollection;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\ProductCompletenessCollection;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProduct;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProductList;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Standard\DateTimeNormalizer;

/**
 * This connector is the equivalent of the ConnectorProductNormalizer, with some differences:
 * - The associated products return uuid instead of identifiers
 * - The quantified associated products return uuid+quantity instead of identifiers+quantity
 * - The identifier attribute value is not removed from the value collection
 * - The identifier attribute is not normalized at the root of the normalized product
 * - The uuid is normalized at the root of the normalized product
 *
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ConnectorProductWithUuidNormalizer
{
    public function __construct(
        private ValuesNormalizer $valuesNormalizer,
        private DateTimeNormalizer $dateTimeNormalizer
    ) {
    }

    public function normalizeConnectorProductList(ConnectorProductList $connectorProducts): array
    {
        $normalizedProducts = [];
        foreach ($connectorProducts->connectorProducts() as $connectorProduct) {
            $normalizedProducts[] = $this->normalizeConnectorProduct($connectorProduct);
        }

        return $normalizedProducts;
    }

    public function normalizeConnectorProduct(ConnectorProduct $connectorProduct): array
    {
        $values = $this->valuesNormalizer->normalize($connectorProduct->values());
        $qualityScores = $connectorProduct->qualityScores();
        $completenesses = $connectorProduct->completenesses();
        $associations = $this->normalizeAssociations($connectorProduct->associations());
        $quantifiedAssociations = $this->normalizeQuantifiedAssociations($connectorProduct->quantifiedAssociations());

        $normalizedProduct =  [
            'uuid' => $connectorProduct->uuid()->toString(),
            'enabled' => $connectorProduct->enabled(),
            'family' => $connectorProduct->familyCode(),
            'categories' => $connectorProduct->categoryCodes(),
            'groups' => $connectorProduct->groupCodes(),
            'parent' => $connectorProduct->parentProductModelCode(),
            'values' => empty($values) ? (object) [] : $values,
            'created' => $this->dateTimeNormalizer->normalize($connectorProduct->createdDate()),
            'updated' => $this->dateTimeNormalizer->normalize($connectorProduct->updatedDate()),
            'associations' => empty($associations) ? (object) [] : $associations,
            'quantified_associations' => empty($quantifiedAssociations) ? (object) [] : $quantifiedAssociations,
        ];

        if ($qualityScores !== null) {
            $normalizedProduct['quality_scores'] = $this->normalizeQualityScores($qualityScores);
        }
        if ($completenesses !== null) {
            $normalizedProduct['completenesses'] = $this->normalizeCompletenesses($completenesses);
        }

        if (!empty($connectorProduct->metadata())) {
            $normalizedProduct['metadata'] = $connectorProduct->metadata();
        }

        return $normalizedProduct;
    }

    private function normalizeQualityScores(QualityScoreCollection $qualityScoreCollection): array
    {
        $qualityScores = [];

        foreach ($qualityScoreCollection->qualityScores as $channel => $localeScores) {
            foreach ($localeScores as $locale => $score) {
                $qualityScores[] = [
                    'scope' => $channel,
                    'locale' => $locale,
                    'data' => $score->getLetter(),
                ];
            }
        }

        return $qualityScores;
    }

    private function normalizeCompletenesses(ProductCompletenessCollection $completenessCollection): array
    {
        $completenesses = [];
        foreach ($completenessCollection as $completeness) {
            $completenesses[] = [
                'scope' => $completeness->channelCode(),
                'locale' => $completeness->localeCode(),
                'data' => $completeness->ratio(),
            ];
        }

        return $completenesses;
    }

    /**
     * This method keeps only the uuid value from the associated products.
     *
     * @param array $associations
     * Example
     * [
     *   'X_SELL' => [
     *     'products' => [['uuid' => '95341071-a0dd-47c6-81b1-315913952c43', 'identifier' => 'product_code_1']],
     *     'product_models' => [],
     *     'groups' => ['group_code_2']
     *   ],
     * ],
     *
     * @return array
     */
    private function normalizeAssociations(array $associations): array
    {
        $result = [];
        foreach ($associations as $associationType => $associationsByType) {
            $result[$associationType] = [];
            foreach ($associationsByType as $entityType => $associationsByEntityType) {
                $result[$associationType][$entityType] = $entityType === 'products' ?
                    array_map(fn (array $associatedObject): ?string => $associatedObject['uuid'], $associationsByEntityType) :
                    $associationsByEntityType;
            }
        }

        return $result;
    }

    /**
     * This method keeps only uuid identifier and quantity values from the associated products.
     *
     * @param array $quantifiedAssociations
     * Example
     * [
     *   'X_SELL' => [
     *     'products' => [
     *       ['uuid' => '95341071-a0dd-47c6-81b1-315913952c43', 'identifier' => 'product_code_1', 'quantity' => 8]
     *     ],
     *     'product_models' => [],
     *     'groups' => ['group_code_2']
     *   ],
     * ],
     *
     * @return array
     */
    private function normalizeQuantifiedAssociations(array $quantifiedAssociations): array
    {
        $result = [];
        foreach ($quantifiedAssociations as $associationType => $associationsByType) {
            foreach ($associationsByType as $entityType => $associationsByEntityType) {
                $result[$associationType][$entityType] = $entityType === 'products' ?
                    array_map(
                        fn (array $associatedObject): array => array_filter(
                            $associatedObject,
                            fn (string $key): bool => in_array($key, ['uuid', 'quantity']),
                            ARRAY_FILTER_USE_KEY
                        ),
                        $associationsByEntityType
                    ) :
                    $result[$associationType][$entityType] = $associationsByEntityType;
            }
        }

        return $result;
    }
}
