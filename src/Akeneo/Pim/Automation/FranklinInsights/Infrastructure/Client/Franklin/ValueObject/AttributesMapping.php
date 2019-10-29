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

namespace Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\ValueObject;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class AttributesMapping implements \IteratorAggregate
{
    /** @var AttributeMapping[] */
    private $attributes;

    /**
     * @param AttributeMapping[]
     */
    public function __construct(array $attributesMapping = [])
    {
        $this->attributes = array_filter($attributesMapping, function ($attributeMapping) {
            return $attributeMapping instanceof AttributeMapping;
        });
    }

    public function add(AttributeMapping $attributeMapping): self
    {
        $this->attributes[] = $attributeMapping;

        return $this;
    }

    /**
     * @return \Iterator
     */
    public function getIterator(): \Iterator
    {
        return new \ArrayIterator($this->attributes);
    }
}
