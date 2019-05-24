<?php
declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Normalizer\ExternalApi;

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
        $values = $this->valuesNormalizer->normalize($connectorProductModel->values(), 'standard');
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
        ];

        if (!empty($connectorProductModel->metadata())) {
            $normalizedProductModel['metadata'] = $connectorProductModel->metadata();
        }

        return $normalizedProductModel;
    }
}
