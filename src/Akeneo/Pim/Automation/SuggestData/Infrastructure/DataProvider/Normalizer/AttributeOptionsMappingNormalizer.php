<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\SuggestData\Infrastructure\DataProvider\Normalizer;

use Akeneo\Pim\Automation\SuggestData\Domain\AttributeOption\Model\Write\AttributeOption;
use Akeneo\Pim\Automation\SuggestData\Domain\AttributeOption\Model\Write\AttributeOptionsMapping;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\Franklin\ValueObject\OptionMapping;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class AttributeOptionsMappingNormalizer
{
    /**
     * @param AttributeOptionsMapping $attributeOptionsMapping
     *
     * @return array
     */
    public function normalize(AttributeOptionsMapping $attributeOptionsMapping): array
    {
        $result = [];

        foreach ($attributeOptionsMapping as $optionMapping) {
            /* @var AttributeOption $optionMapping */
            $result[] = [
                'from' => [
                    'id' => $optionMapping->getFranklinOptionId(),
                    'label' => [
                        'en_US' => $optionMapping->getFranklinOptionLabel(),
                    ],
                ],
                'to' => $this->computeTargetOptionMapping($optionMapping),
                'status' => $optionMapping->isMapped() ? OptionMapping::STATUS_ACTIVE : OptionMapping::STATUS_INACTIVE,
            ];
        }

        return $result;
    }

    private function computeTargetOptionMapping(AttributeOption $optionMapping)
    {
        $to = null;
        if ($optionMapping->isMapped()) {
            $pimLabel = null;
            if (!empty($optionMapping->getPimOptionLabel())) {
                $pimLabel = ['en_US' => $optionMapping->getPimOptionLabel()];
            }

            $to = [
                'id' => $optionMapping->getPimOptionId(),
                'label' => $pimLabel,
            ];
        }

        return $to;
    }
}
