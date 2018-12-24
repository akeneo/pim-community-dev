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

namespace Akeneo\Pim\Automation\SuggestData\Application\DataProvider;

use Akeneo\Pim\Automation\SuggestData\Domain\AttributeMapping\Model\Read\AttributesMappingResponse;

/**
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
interface AttributesMappingProviderInterface
{
    /**
     * @param string $familyCode
     *
     * @return AttributesMappingResponse
     */
    public function getAttributesMapping(string $familyCode): AttributesMappingResponse;

    /**
     * @param string $familyCode
     * @param array $attributesMapping
     */
    public function saveAttributesMapping(string $familyCode, array $attributesMapping): void;
}
