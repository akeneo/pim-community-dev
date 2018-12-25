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

namespace Akeneo\Pim\Automation\FranklinInsights\Infrastructure\DataProvider\Converter;

use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeOption\Model\Read\AttributeOptionMapping;
use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeOption\Model\Read\AttributeOptionsMapping;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\ValueObject\OptionMapping
    as FranklinAttributeOptionMapping;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\ValueObject\OptionsMapping
    as FranklinAttributeOptionsMapping;

/**
 * Converts object AttributeOptionsMapping between Franklin client and Akeneo PIM Application layer.
 *
 * @author Romain Monceau <romain@akeneo.com>
 */
final class AttributeOptionsMappingConverter
{
    /**
     * @param string $familyCode
     * @param string $franklinAttributeId
     * @param FranklinAttributeOptionsMapping $franklinAttrOptionsMapping
     *
     * @return AttributeOptionsMapping
     */
    public function clientToApplication(
        string $familyCode,
        string $franklinAttributeId,
        FranklinAttributeOptionsMapping $franklinAttrOptionsMapping
    ): AttributeOptionsMapping {
        $pimOptionsMapping = [];
        foreach ($franklinAttrOptionsMapping as $franklinOptionMapping) {
            $pimStatus = $this->convertClientStatusToApplicationStatus($franklinOptionMapping->getStatus());

            $pimOptionsMapping[] = new AttributeOptionMapping(
                $franklinOptionMapping->getFranklinOptionId(),
                $franklinOptionMapping->getFranklinOptionLabel(),
                $pimStatus,
                $franklinOptionMapping->getPimOption()
            );
        }

        return new AttributeOptionsMapping($familyCode, $franklinAttributeId, $pimOptionsMapping);
    }

    /**
     * @param string $clientStatus
     *
     * @return int
     */
    private function convertClientStatusToApplicationStatus(string $clientStatus)
    {
        if (FranklinAttributeOptionMapping::STATUS_PENDING === $clientStatus) {
            return AttributeOptionMapping::STATUS_PENDING;
        }
        if (FranklinAttributeOptionMapping::STATUS_ACTIVE === $clientStatus) {
            return AttributeOptionMapping::STATUS_ACTIVE;
        }
        if (FranklinAttributeOptionMapping::STATUS_INACTIVE === $clientStatus) {
            return AttributeOptionMapping::STATUS_INACTIVE;
        }
    }
}
