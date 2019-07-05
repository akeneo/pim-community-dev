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


namespace Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeMapping\Query;

use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\FamilyCode;

/**
 * @author Olivier Pontier <olivier.pontier@akeneo.com>
 */
interface SelectExactMatchAttributeCodesFromOtherFamiliesQueryInterface
{
    /**
     * @param FamilyCode $familyCode
     * @param array[string] $franklinAttributeLabels
     * @return array
     *
     * @example $franklinAttributeLabels = ['Matched Franklin label', 'Color', 'Not Matched Franklin label', 'Weight']
     *          returns: ['Matched Franklin label' => 'pim_matched_attribute_code', 'Color' => 'color', 'Not Matched Franklin label' => null, 'Weight' => null]
     */
    public function execute(FamilyCode $familyCode, array $franklinAttributeLabels): array;
}
