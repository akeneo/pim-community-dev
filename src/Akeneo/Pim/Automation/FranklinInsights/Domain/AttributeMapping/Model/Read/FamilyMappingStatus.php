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

namespace Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeMapping\Model\Read;

use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\Model\Read\Family;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class FamilyMappingStatus
{
    /* Some of the attributes are still pending */
    public const MAPPING_PENDING = 0;

    /** All attributes are either mapped or unmapped */
    public const MAPPING_FULL = 1;

    /** There is no attributes to be mapped */
    public const MAPPING_EMPTY = 2;

    /** @var Family */
    private $family;

    /** @var int */
    private $mappingStatus;

    /**
     * @param Family $family
     * @param int    $mappingStatus
     */
    public function __construct(Family $family, int $mappingStatus)
    {
        $this->family = $family;
        $this->mappingStatus = $mappingStatus;
    }

    /**
     * @return Family
     */
    public function getFamily(): Family
    {
        return $this->family;
    }

    /**
     * @return int
     */
    public function getMappingStatus(): int
    {
        return $this->mappingStatus;
    }
}
