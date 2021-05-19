<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\DataQualityInsights\Application;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Structure\AttributeOptionSpellcheck;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Structure\GetAllAttributeOptionsSpellcheckQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Structure\GetAttributeSpellcheckQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeCode;

class GetAttributeSpellcheckEvaluation
{
    private const ATTRIBUTE_OPTION_QUERY_BATCH_SIZE = 10000;

    private GetAllAttributeOptionsSpellcheckQueryInterface $attributeOptionsSpellcheckQuery;
    private GetAttributeSpellcheckQueryInterface $attributeSpellcheckQuery;

    public function __construct(
        GetAllAttributeOptionsSpellcheckQueryInterface $attributeOptionsSpellcheckQuery,
        GetAttributeSpellcheckQueryInterface $attributeSpellcheckQuery
    ) {
        $this->attributeOptionsSpellcheckQuery = $attributeOptionsSpellcheckQuery;
        $this->attributeSpellcheckQuery = $attributeSpellcheckQuery;
    }

    public function get(AttributeCode $attributeCode): array
    {
        $attributeSpellcheck = $this->attributeSpellcheckQuery->getByAttributeCode($attributeCode);

        $options = [];
        $optionsCount = 0;
        $lastAttributeOptionCode = null;
        do {
            $attributeOptionsSpellcheck = $this->attributeOptionsSpellcheckQuery->byAttributeCode(
                $attributeCode,
                self::ATTRIBUTE_OPTION_QUERY_BATCH_SIZE,
                $lastAttributeOptionCode
            );

            /** @var AttributeOptionSpellcheck $attributeOptionSpellcheck */
            foreach ($attributeOptionsSpellcheck as $attributeOptionSpellcheck) {
                $lastAttributeOptionCode = strval($attributeOptionSpellcheck->getAttributeOptionCode());
                $options[$lastAttributeOptionCode] = [
                    'toImprove' => $attributeOptionSpellcheck->getResult()->getLabelsToImproveNumber(),
                    'locales' => $attributeOptionSpellcheck->getResult()->toArrayBool()
                ];
                $optionsCount += $attributeOptionSpellcheck->getResult()->getLabelsToImproveNumber();
            }
        } while (count($attributeOptionsSpellcheck) === self::ATTRIBUTE_OPTION_QUERY_BATCH_SIZE);

        $labelsCount = 0;
        $labels = [];

        if ($attributeSpellcheck !== null) {
            $labelsCount = $attributeSpellcheck->getResult()->getLabelsToImproveNumber();
            $labels = $attributeSpellcheck->getResult()->toArrayBool();
        }

        return [
            'attribute' => strval($attributeCode),
            'options_count' => $optionsCount,
            'options' => $options,
            'labels_count' => $labelsCount,
            'labels' => $labels,
        ];
    }
}
