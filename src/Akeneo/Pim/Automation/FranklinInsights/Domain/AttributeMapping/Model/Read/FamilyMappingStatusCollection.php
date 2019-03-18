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

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class FamilyMappingStatusCollection implements \IteratorAggregate, \Countable
{
    /** @var FamilyMappingStatus[] */
    private $familiesMappingStatus;

    public function __construct()
    {
        $this->familiesMappingStatus = [];
    }

    /**
     * @param FamilyMappingStatus $familyMappingStatus
     *
     * @return FamilyMappingStatusCollection
     */
    public function add(FamilyMappingStatus $familyMappingStatus): self
    {
        $this->familiesMappingStatus[] = $familyMappingStatus;

        return $this;
    }

    /**
     * @return \ArrayIterator
     */
    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->familiesMappingStatus);
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return count($this->familiesMappingStatus);
    }
}
