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

namespace Akeneo\Pim\Automation\SuggestData\Domain\AttributeMapping\Model\Read;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class FamilyCollection implements \IteratorAggregate, \Countable
{
    /** @var Family[] */
    private $families;

    public function __construct()
    {
        $this->families = [];
    }

    /**
     * @param Family $family
     *
     * @return FamilyCollection
     */
    public function add(Family $family): self
    {
        $this->families[] = $family;

        return $this;
    }

    /**
     * @return \Iterator
     */
    public function getIterator(): \Iterator
    {
        return new \ArrayIterator($this->families);
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return count($this->families);
    }
}
