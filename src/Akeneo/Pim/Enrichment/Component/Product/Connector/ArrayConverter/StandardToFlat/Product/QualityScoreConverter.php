<?php

declare(strict_types=1);

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\StandardToFlat\Product;

use Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase\GetProductsWithQualityScoresInterface;

class QualityScoreConverter
{
    /**
     * Convert standard quality score to flat formatted one
     * Given this $data :
     * [
     *      "ecommerce" => [
     *          'en_US' => "B",
     *          'fr_FR' => "C"
     *      ],
     *      "mobile" => [
     *          'en_US' => "E",
     *          'fr_FR' => "A"
     *      ]
     * ]
     *
     * It will return :
     * [
     *      'dqi_quality_score-en_US-ecommerce' => "B",
     *      'dqi_quality_score-fr_FR-ecommerce' => "C",
     *      'dqi_quality_score-en_US-mobile' => "E",
     *      'dqi_quality_score-fr_FR-mobile' => "A",
     * ]
     * @see GetProductsWithQualityScoresInterface::FLAT_FIELD_PREFIX
     */
    public function convert(array $scores): array
    {
        $convertedItems = [];
        foreach ($scores as $channel => $localeScores) {
            foreach ($localeScores as $locale => $score) {
                $propertyName = sprintf(
                    '%s-%s-%s',
                    GetProductsWithQualityScoresInterface::FLAT_FIELD_PREFIX,
                    $locale,
                    $channel
                );

                $convertedItems[$propertyName] = $score;
            }
        }

        return $convertedItems;
    }
}
