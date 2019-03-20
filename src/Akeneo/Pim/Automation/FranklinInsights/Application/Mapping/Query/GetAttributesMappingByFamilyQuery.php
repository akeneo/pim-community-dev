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

namespace Akeneo\Pim\Automation\FranklinInsights\Application\Mapping\Query;

use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\FamilyCode;

/**
 * Query the attributes mapping of a family.
 *
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class GetAttributesMappingByFamilyQuery
{
    /** @var FamilyCode */
    private $familyCode;

    public function __construct(FamilyCode $familyCode)
    {
        $this->familyCode = $familyCode;
    }

    public function getFamilyCode(): FamilyCode
    {
        return $this->familyCode;
    }
}
