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

namespace Akeneo\Pim\Automation\SuggestData\Infrastructure\DataProvider\Converter\AttributeOptionsMapping;

use Akeneo\Pim\Automation\SuggestData\Domain\Model\Read\AttributeOptionMapping as AppAttributeOptionMapping;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\Read\AttributeOptionsMapping as AppAttributeOptionsMapping;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\ValueObject\AttributeOptionMapping
    as FranklinAttributeOptionMapping;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\ValueObject\AttributeOptionsMapping
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
     * @return AppAttributeOptionsMapping
     */
    public function clientToApplication(
        string $familyCode,
        string $franklinAttributeId,
        FranklinAttributeOptionsMapping $franklinAttrOptionsMapping
    ): AppAttributeOptionsMapping {
        $pimOptionsMapping = [];
        foreach ($franklinAttrOptionsMapping as $franklinOptionMapping) {
            $pimStatus = self::convertClientStatusToApplicationStatus($franklinOptionMapping->getStatus());

            $pimOptionsMapping[] = new AppAttributeOptionMapping(
                $franklinOptionMapping->getFranklinOptionId(),
                $franklinOptionMapping->getFranklinOptionLabel(),
                $pimStatus,
                $franklinOptionMapping->getPimOption()
            );
        }

        return new AppAttributeOptionsMapping((string) $familyCode, (string) $franklinAttributeId, $pimOptionsMapping);
    }

    /**
     * @param string $clientStatus
     *
     * @return int
     */
    private function convertClientStatusToApplicationStatus(string $clientStatus)
    {
        if (FranklinAttributeOptionMapping::STATUS_PENDING === $clientStatus) {
            return AppAttributeOptionMapping::STATUS_PENDING;
        } elseif (FranklinAttributeOptionMapping::STATUS_ACTIVE === $clientStatus) {
            return AppAttributeOptionMapping::STATUS_ACTIVE;
        } elseif (FranklinAttributeOptionMapping::STATUS_INACTIVE === $clientStatus) {
            return AppAttributeOptionMapping::STATUS_INACTIVE;
        }
    }
}
