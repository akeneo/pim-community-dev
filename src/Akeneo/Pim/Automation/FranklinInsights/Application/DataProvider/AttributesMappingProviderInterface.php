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

use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeMapping\Model\Read\AttributesMappingResponse;
use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeMapping\Model\Write\AttributesMapping;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\Exception\DataProviderException;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\FamilyCode;

/**
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
interface AttributesMappingProviderInterface
{
    /**
     * @param FamilyCode $familyCode
     *
     * @throws DataProviderException
     *
     * @return AttributesMappingResponse
     */
    public function getAttributesMapping(FamilyCode $familyCode): AttributesMappingResponse;

    /**
     * @param FamilyCode        $familyCode
     * @param AttributesMapping $attributesMapping
     *
     * @throws DataProviderException
     */
    public function saveAttributesMapping(FamilyCode $familyCode, AttributesMapping $attributesMapping): void;
}
