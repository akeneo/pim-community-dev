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
    /** @var GetAllAttributeOptionsSpellcheckQueryInterface */
    private $attributeOptionsSpellcheckQuery;

    /** @var GetAttributeSpellcheckQueryInterface */
    private $attributeSpellcheckQuery;

    public function __construct(
        GetAllAttributeOptionsSpellcheckQueryInterface $attributeOptionsSpellcheckQuery,
        GetAttributeSpellcheckQueryInterface $attributeSpellcheckQuery
    ) {
        $this->attributeOptionsSpellcheckQuery = $attributeOptionsSpellcheckQuery;
        $this->attributeSpellcheckQuery = $attributeSpellcheckQuery;
    }

    public function get(AttributeCode $attributeCode): array
    {
        $attributeOptionsSpellcheck = $this->attributeOptionsSpellcheckQuery->byAttributeCode($attributeCode);
        $attributeSpellcheck = $this->attributeSpellcheckQuery->getByAttributeCode($attributeCode);

        $options = array_reduce($attributeOptionsSpellcheck, function (array $previousData, AttributeOptionSpellcheck $attributeOptionSpellcheck) {
            return array_replace($previousData, [
                strval($attributeOptionSpellcheck->getAttributeOptionCode()) => [
                    'toImprove' => $attributeOptionSpellcheck->getResult()->getLabelsToImproveNumber(),
                    'locales' => $attributeOptionSpellcheck->getResult()->toArrayBool()
                ]
            ]);
        }, []);

        $optionsCount = array_reduce($attributeOptionsSpellcheck, function (int $previousCount, AttributeOptionSpellcheck $attributeOptionSpellcheck) {
            return $previousCount + $attributeOptionSpellcheck->getResult()->getLabelsToImproveNumber();
        }, 0);

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
