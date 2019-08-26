<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\FranklinInsights\Application\QualityHighlights;

use Akeneo\Pim\Automation\FranklinInsights\Application\DataProvider\QualityHighlightsProviderInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeMapping\Model\Write\AttributeMapping;
use Akeneo\Pim\Automation\FranklinInsights\Domain\QualityHighlights\Query\SelectAttributeOptionsByAttributeCodeQueryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\QualityHighlights\Query\SelectAttributesToApplyQueryInterface;
use Akeneo\Pim\Structure\Component\AttributeTypes;

class ApplyAttributeStructure
{
    /** @var SelectAttributesToApplyQueryInterface */
    private $selectAttributesToApplyQuery;

    /** @var QualityHighlightsProviderInterface */
    private $qualityHighlightsProvider;

    /** @var SelectAttributeOptionsByAttributeCodeQueryInterface */
    private $selectAttributeOptions;

    public function __construct(
        SelectAttributesToApplyQueryInterface $selectAttributesToApplyQuery,
        QualityHighlightsProviderInterface $qualityHighlightsProvider,
        SelectAttributeOptionsByAttributeCodeQueryInterface $selectAttributeOptions
    ) {
        $this->selectAttributesToApplyQuery = $selectAttributesToApplyQuery;
        $this->qualityHighlightsProvider = $qualityHighlightsProvider;
        $this->selectAttributeOptions = $selectAttributeOptions;
    }

    public function apply(array $attributeIds): void
    {
        $attributes = $this->selectAttributesToApplyQuery->execute($attributeIds);
        if (empty($attributes)) {
            return;
        }

        foreach ($attributes as $index => $attribute) {
            if ($attribute['type'] === AttributeTypes::OPTION_SIMPLE_SELECT || $attribute['type'] === AttributeTypes::OPTION_MULTI_SELECT) {
                $attributeOptions = $this->selectAttributeOptions->execute($attribute['code']);
                if (! empty($attributeOptions)) {
                    $attributes[$index]['options'] = $attributeOptions;
                }
            }
        }
        $attributes = $this->convertPimAttributeTypesToFranklinTypes($attributes);

        $this->qualityHighlightsProvider->applyAttributeStructure(['attributes' => $attributes]);
    }

    private function convertPimAttributeTypesToFranklinTypes(array $attributes): array
    {
        foreach ($attributes as $index => $attribute) {
            $attributes[$index]['type'] = AttributeMapping::AUTHORIZED_ATTRIBUTE_TYPE_MAPPINGS[$attribute['type']];
        }

        return $attributes;
    }
}
