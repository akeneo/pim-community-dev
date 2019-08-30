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

namespace Akeneo\Pim\Automation\FranklinInsights\Application\Structure\Command;

use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\AttributeCode;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\FamilyCode;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class BulkAddAttributesToFamilyCommand
{
    private $familyCode;

    private $attributeCodes;

    public function __construct(FamilyCode $familyCode, array $attributeCodes)
    {
        $this->familyCode = $familyCode;
        $this->attributeCodes = $attributeCodes;
    }

    public function getFamilyCode(): FamilyCode
    {
        return $this->familyCode;
    }

    /**
     * @return AttributeCode[]
     */
    public function getAttributeCodes(): array
    {
        return $this->attributeCodes;
    }
}
