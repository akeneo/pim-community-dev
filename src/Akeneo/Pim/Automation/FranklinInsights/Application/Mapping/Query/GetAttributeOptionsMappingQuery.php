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
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\FranklinAttributeId;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
class GetAttributeOptionsMappingQuery
{
    /** @var FamilyCode */
    private $familyCode;

    /** @var FranklinAttributeId */
    private $franklinAttributeId;

    /**
     * @param FamilyCode $familyCode
     * @param FranklinAttributeId $franklinAttributeId
     */
    public function __construct(FamilyCode $familyCode, FranklinAttributeId $franklinAttributeId)
    {
        $this->familyCode = $familyCode;
        $this->franklinAttributeId = $franklinAttributeId;
    }

    /**
     * @return FamilyCode
     */
    public function familyCode(): FamilyCode
    {
        return $this->familyCode;
    }

    /**
     * @return FranklinAttributeId
     */
    public function franklinAttributeId(): FranklinAttributeId
    {
        return $this->franklinAttributeId;
    }
}
