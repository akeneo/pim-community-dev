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

use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\AttributeCode;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class AttributeOptionsMapping implements \IteratorAggregate
{
    /** @var AttributeCode */
    private $attributeCode;

    /** @var array */
    private $attributeOptions;

    public function __construct(AttributeCode $attributeCode)
    {
        $this->attributeCode = $attributeCode;
        $this->attributeOptions = [];
    }

    public function getAttributeCode(): AttributeCode
    {
        return $this->attributeCode;
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
     * @return array
     */
    public function getOptionCodes(): array
    {
        return array_filter(
            array_map(function (AttributeOption $option) {
                return $option->getPimOptionId();
            }, $this->attributeOptions)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator(): \Iterator
    {
        return new \ArrayIterator($this->attributeOptions);
    }
}
