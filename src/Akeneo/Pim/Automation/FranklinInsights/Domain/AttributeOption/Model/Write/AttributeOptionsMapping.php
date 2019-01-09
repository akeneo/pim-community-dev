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

namespace Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeOption\Model\Write;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class AttributeOptionsMapping implements \IteratorAggregate
{
    /** @var array */
    private $attributeOptions;

    public function __construct()
    {
        $this->attributeOptions = [];
    }

    /**
     * @param AttributeOption $attributeOption
     *
     * @return AttributeOptionsMapping
     */
    public function addAttributeOption(AttributeOption $attributeOption): self
    {
        $this->attributeOptions[] = $attributeOption;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator(): \Iterator
    {
        return new \ArrayIterator($this->attributeOptions);
    }
}
