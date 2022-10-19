<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Normalizer\ExternalApi;

use Akeneo\Pim\Automation\DataQualityInsights\PublicApi\Model\QualityScoreCollection;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\ProductCompletenessCollection;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProduct;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProductList;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Standard\DateTimeNormalizer;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;

/**
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @author    Mathias Métayer <mathias.metayer@akeneo.com>
 * @author    Alexandre Hocquard <alexandre.hocquard@akeneo.com>
 *
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ConnectorProductNormalizer
{
    public function __construct(
        private ValuesNormalizer $valuesNormalizer,
        private DateTimeNormalizer $dateTimeNormalizer,
        private AttributeRepositoryInterface $attributeRepository
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
        $values = $this->removeIdentifierValue($values);
        $qualityScores = $connectorProduct->qualityScores();
        $completenesses = $connectorProduct->completenesses();
        $associations = $this->normalizeAssociations($connectorProduct->associations());
        $quantifiedAssociations = $this->normalizeQuantifiedAssociations($connectorProduct->quantifiedAssociations());

        $normalizedProduct =  [
            'uuid' => $connectorProduct->uuid(),
            'identifier' => $connectorProduct->identifier(),
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

    private function removeIdentifierValue(array $values): array
    {
        $identifierCode = $this->attributeRepository->getIdentifierCode();
        unset($values[$identifierCode]);

        return $values;
    }

    /**
     * This method keeps only the identifier value from the associated products and removes the uuid from result.
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
                    array_map(fn (array $associatedObject): ?string => $associatedObject['identifier'], $associationsByEntityType) :
                    $associationsByEntityType;
            }
        }

        return $result;
    }

    /**
     * This method keeps only the identifier and quantity values from the associated products and removes the uuid from
     * result.
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
                            fn (string $key): bool => in_array($key, ['identifier', 'quantity']),
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
