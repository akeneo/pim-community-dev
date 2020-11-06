<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\Model;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeType;

final class ProductValuesCollection implements \IteratorAggregate
{
    /** @var array */
    private $productValuesByAttribute;

    public function __construct()
    {
        $this->productValuesByAttribute = [];
    }

    public function add(ProductValues $productValues): self
    {
        $this->productValuesByAttribute[strval($productValues->getAttribute()->getCode())] = $productValues;

        return $this;
    }

    public function getTextValues(): \Iterator
    {
        $textType = AttributeType::text();
        foreach ($this->productValuesByAttribute as $productValues) {
            if ($productValues->getAttribute()->getType()->equals($textType)) {
                yield $productValues;
            }
        }
    }

    public function getLocalizableTextValues(): \Iterator
    {
        foreach ($this->getTextValues() as $textareaProductValues) {
            if ($textareaProductValues->getAttribute()->isLocalizable()) {
                yield $textareaProductValues;
            }
        }
    }

    public function getTextareaValues(): \Iterator
    {
        $textareaType = AttributeType::textarea();
        foreach ($this->productValuesByAttribute as $productValues) {
            if ($productValues->getAttribute()->getType()->equals($textareaType)) {
                yield $productValues;
            }
        }
    }

    public function getLocalizableTextareaValues(): \Iterator
    {
        foreach ($this->getTextareaValues() as $textareaProductValues) {
            if ($textareaProductValues->getAttribute()->isLocalizable()) {
                yield $textareaProductValues;
            }
        }
    }

    public function getSimpleSelectValues(): \Iterator
    {
        $simpleSelectType = AttributeType::simpleSelect();
        foreach ($this->productValuesByAttribute as $productValues) {
            if ($productValues->getAttribute()->getType()->equals($simpleSelectType)) {
                yield $productValues;
            }
        }
    }

    public function getMultiSelectValues(): \Iterator
    {
        $multiSelectType = AttributeType::multiSelect();
        foreach ($this->productValuesByAttribute as $productValues) {
            if ($productValues->getAttribute()->getType()->equals($multiSelectType)) {
                yield $productValues;
            }
        }
    }

    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->productValuesByAttribute);
    }
}
