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

namespace Akeneo\Pim\Automation\FranklinInsights\Domain\QualityHighlights\Model\Read;

use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\FamilyCode;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\ProductId;

class Product
{
    /** @var ProductId */
    private $id;

    /** @var FamilyCode */
    private $familyCode;

    /** @var array */
    private $rawValues;

    public function __construct(ProductId $id, FamilyCode $familyCode, array $rawValues)
    {
        $this->id = $id;
        $this->familyCode = $familyCode;
        $this->rawValues = $rawValues;
    }

    public function getId(): ProductId
    {
        return $this->id;
    }

    public function getFamilyCode(): FamilyCode
    {
        return $this->familyCode;
    }

    public function getRawValues(): array
    {
        return $this->rawValues;
    }
}
