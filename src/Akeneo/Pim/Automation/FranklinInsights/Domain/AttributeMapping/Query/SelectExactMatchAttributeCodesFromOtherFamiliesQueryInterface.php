<?php

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
    public function execute(FamilyCode $familyCode, array $franklinAttributeLabels): array;
}
