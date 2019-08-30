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

use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\FamilyCode;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Structure\ValueObject\AttributesToCreate;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class BulkCreateAttributesInFamilyCommand
{
    private $pimFamilyCode;

    private $attributesToCreate;

    public function __construct(FamilyCode $pimFamilyCode, AttributesToCreate $attributesToCreate)
    {
        $this->pimFamilyCode = $pimFamilyCode;
        $this->attributesToCreate = $attributesToCreate;
    }

    public function getPimFamilyCode(): FamilyCode
    {
        return $this->pimFamilyCode;
    }

    public function getAttributesToCreate(): AttributesToCreate
    {
        return $this->attributesToCreate;
    }
}
