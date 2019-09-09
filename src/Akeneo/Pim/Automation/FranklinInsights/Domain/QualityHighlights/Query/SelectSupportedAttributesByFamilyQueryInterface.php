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

namespace Akeneo\Pim\Automation\FranklinInsights\Domain\QualityHighlights\Query;

use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\FamilyCode;
use Akeneo\Pim\Automation\FranklinInsights\Domain\QualityHighlights\Model\Read\Attribute;

interface SelectSupportedAttributesByFamilyQueryInterface
{
    /**
     * @param FamilyCode $familyCode
     *
     * @return Attribute[]
     */
    public function execute(FamilyCode $familyCode): array;
}
