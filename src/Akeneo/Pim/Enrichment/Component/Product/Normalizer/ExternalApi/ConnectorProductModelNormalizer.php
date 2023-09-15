<?php
declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Normalizer\ExternalApi;

use Akeneo\Pim\Automation\DataQualityInsights\PublicApi\Model\QualityScoreCollection;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProductModel;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProductModelList;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Standard\DateTimeNormalizer;

/**
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @author    Mathias Métayer <mathias.metayer@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ConnectorProductModelNormalizer
{
    /** @var ValuesNormalizer */
    private $valuesNormalizer;

    /** @var DateTimeNormalizer */
    private $dateTimeNormalizer;

    public function __construct(ValuesNormalizer $valuesNormalizer, DateTimeNormalizer $dateTimeNormalizer)
    {
        $this->valuesNormalizer = $valuesNormalizer;
        $this->dateTimeNormalizer = $dateTimeNormalizer;
    }

    public function normalizeConnectorProductModelList(ConnectorProductModelList $list): array
    {
        return array_map(function (ConnectorProductModel $connectorProductModel): array {
            return $this->normalizeConnectorProductModel($connectorProductModel);
        }, $list->connectorProductModels());
    }

    public function normalizeConnectorProductModel(ConnectorProductModel $connectorProductModel): array
    {
        $values = $this->valuesNormalizer->normalize($connectorProductModel->values());
        $normalizedProductModel = [
            'code' => $connectorProductModel->code(),
            'family' => $connectorProductModel->familyCode(),
            'family_variant' => $connectorProductModel->familyVariantCode(),
            'parent' => $connectorProductModel->parentCode(),
            'categories' => $connectorProductModel->categoryCodes(),
            'values' => empty($values) ? (object) [] : $values,
            'created' => $this->dateTimeNormalizer->normalize($connectorProductModel->createdDate()),
            'updated' => $this->dateTimeNormalizer->normalize($connectorProductModel->updatedDate()),
            'associations' => empty($connectorProductModel->associations()) ? (object) [] : $connectorProductModel->associations(),
            'quantified_associations' => empty($connectorProductModel->quantifiedAssociations()) ? (object) [] : $this->normalizeQuantifiedAssociations($connectorProductModel->quantifiedAssociations()),
        ];

        if (!empty($connectorProductModel->metadata())) {
            $normalizedProductModel['metadata'] = $connectorProductModel->metadata();
        }

        $qualityScores = $connectorProductModel->qualityScores();
        if ($qualityScores !== null) {
            $normalizedProductModel['quality_scores'] = $this->normalizeQualityScores($qualityScores);
        }

        return $normalizedProductModel;
    }

    private function normalizeQualityScores(QualityScoreCollection $productModelScores): array
    {
        $qualityScores = [];

        foreach ($productModelScores->qualityScores as $channel => $localeScores) {
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
