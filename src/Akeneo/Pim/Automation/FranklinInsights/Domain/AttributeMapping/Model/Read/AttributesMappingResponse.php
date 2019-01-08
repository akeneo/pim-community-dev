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

use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\AttributeCode;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class AttributesMappingResponse implements \IteratorAggregate
{
    /** @var array */
    private $attributes;

    public function __construct()
    {
        $this->attributes = [];
    }

    /**
     * @param AttributeMapping $attribute
     *
     * @return AttributesMappingResponse
     */
    public function addAttribute(AttributeMapping $attribute): self
    {
        $this->attributes[] = $attribute;

        return $this;
    }

    /**
     * @param AttributeCode $attributeCode
     *
     * @return bool
     */
    public function hasPimAttribute(AttributeCode $attributeCode): bool
    {
        foreach ($this->attributes as $attribute) {
            if ($attribute->getPimAttributeCode() === (string) $attributeCode) {
                return true;
            }
        }

        return false;
    }

    /**
     * Sort alphabetically the attributes mapping.
     */
    public function sort(): void
    {
        usort($this->attributes, function ($a, $b) {
            return $a->getTargetAttributeLabel() <=> $b->getTargetAttributeLabel();
        });
    }

    /**
     * @return \Iterator
     */
    public function getIterator(): \Iterator
    {
        return new \ArrayIterator($this->attributes);
    }
}
