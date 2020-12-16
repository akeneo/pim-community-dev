<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\Model;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeType;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
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

    public function getIterator()
    {
        return new \ArrayIterator($this->productValuesByAttribute);
    }
}
