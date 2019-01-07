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

namespace Akeneo\Pim\Automation\FranklinInsights\Application\DataProvider;

use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeOption\Model\Read\AttributeOptionsMapping as ReadAttributeOptionsMapping;
use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeOption\Model\Write\AttributeOptionsMapping as WriteAttributeOptionsMapping;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\FamilyCode;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\FranklinAttributeId;

/**
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
interface AttributeOptionsMappingProviderInterface
{
    /**
     * @param FamilyCode $familyCode
     * @param FranklinAttributeId $franklinAttributeId
     *
     * @return ReadAttributeOptionsMapping
     */
    public function getAttributeOptionsMapping(
        FamilyCode $familyCode,
        FranklinAttributeId $franklinAttributeId
    ): ReadAttributeOptionsMapping;

    /**
     * @param FamilyCode $familyCode
     * @param FranklinAttributeId $franklinAttributeId
     * @param WriteAttributeOptionsMapping $attributeOptionsMapping
     */
    public function saveAttributeOptionsMapping(
        FamilyCode $familyCode,
        FranklinAttributeId $franklinAttributeId,
        WriteAttributeOptionsMapping $attributeOptionsMapping
    ): void;
}
