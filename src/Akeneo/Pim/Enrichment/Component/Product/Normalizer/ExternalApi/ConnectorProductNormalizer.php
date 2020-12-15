<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Normalizer\ExternalApi;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleRateCollection;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProduct;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProductList;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Standard\DateTimeNormalizer;

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
    /** @var ValuesNormalizer */
    private $valuesNormalizer;

    /** @var DateTimeNormalizer */
    private $dateTimeNormalizer;

    public function __construct(ValuesNormalizer $valuesNormalizer, DateTimeNormalizer $dateTimeNormalizer)
    {
        $this->valuesNormalizer = $valuesNormalizer;
        $this->dateTimeNormalizer = $dateTimeNormalizer;
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

        $normalizedProduct =  [
            'identifier' => $connectorProduct->identifier(),
            'enabled' => $connectorProduct->enabled(),
            'family' => $connectorProduct->familyCode(),
            'categories' => $connectorProduct->categoryCodes(),
            'groups' => $connectorProduct->groupCodes(),
            'parent' => $connectorProduct->parentProductModelCode(),
            'values' => empty($values) ? (object) [] : $values,
            'created' => $this->dateTimeNormalizer->normalize($connectorProduct->createdDate()),
            'updated' => $this->dateTimeNormalizer->normalize($connectorProduct->updatedDate()),
            'associations' => empty($connectorProduct->associations()) ? (object) [] : $connectorProduct->associations(),
            'quantified_associations' => empty($connectorProduct->quantifiedAssociations()) ? (object) [] : $connectorProduct->quantifiedAssociations(),
        ];

        if ($qualityScores !== null) {
            $normalizedProduct['quality_scores'] = $this->normalizeQualityScores($qualityScores);
        }

        if (!empty($connectorProduct->metadata())) {
            $normalizedProduct['metadata'] = $connectorProduct->metadata();
        }

        return $normalizedProduct;
    }

    private function normalizeQualityScores(ChannelLocaleRateCollection $channelLocaleRates): array
    {
        $qualityScores = [];

        foreach ($channelLocaleRates as $channel => $localeRates) {
            foreach ($localeRates as $locale => $rate) {
                $qualityScores[] = [
                    'scope' => $channel,
                    'locale' => $locale,
                    'data' => $rate->toLetter(),
                ];
            }
        }

        return $qualityScores;
    }
}
