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

namespace Akeneo\Pim\Automation\SuggestData\Domain\Model\Write;

/**
 * @author    Romain Monceau <romain@akeneo.com>
 */
class AttributesMapping implements \IteratorAggregate
{
    /** @var AttributeMapping[] */
    private $collection = [];

    /**
     * @param AttributeMapping $attributeMapping
     *
     * @return AttributesMapping
     */
    public function addAttributeMapping(AttributeMapping $attributeMapping): self
    {
        $this->collection[] = $attributeMapping;

        return $this;
    }

    /**
     * @return \Iterator
     */
    public function getIterator(): \Iterator
    {
        return new \ArrayIterator($this->collection);
    }
}
