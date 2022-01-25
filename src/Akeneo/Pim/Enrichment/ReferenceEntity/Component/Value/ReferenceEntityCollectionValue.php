<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Enrichment\ReferenceEntity\Component\Value;

use Akeneo\Pim\Enrichment\Component\Product\Model\AbstractValue;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;

/**
 * Product value for reference entity
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class ReferenceEntityCollectionValue extends AbstractValue implements ReferenceEntityCollectionValueInterface
{
    /**
     * {@inheritdoc}
     */
    protected function __construct(string $attributeCode, ?array $recordCodes, ?string $scopeCode, ?string $localeCode)
    {
        parent::__construct($attributeCode, $recordCodes, $scopeCode, $localeCode);
    }

    /**
     * @return RecordCode[]
     */
    public function getData(): ?array
    {
        return $this->data;
    }

    /**
     * {@inheritdoc}
     */
    public function isEqual(ValueInterface $other): bool
    {
        if ($this->scopeCode !== $other->getScopeCode()
            || $this->localeCode !== $other->getLocaleCode()
        ) {
            return false;
        }

        if (count($this->getData()) !== count($other->getData())) {
            return false;
        }

        $iterator = new \MultipleIterator();
        $iterator->attachIterator(new \ArrayIterator($this->getData()));
        $iterator->attachIterator(new \ArrayIterator($other->getData()));

        foreach ($iterator as $iteratorValue) {
            if (!$iteratorValue[0]->equals($iteratorValue[1])) {
                return false;
            }
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function __toString(): string
    {
        return null !== $this->data ? implode(
            ', ',
            array_map(
                function (RecordCode $recordCode) {
                    return $recordCode->__toString();
                },
                $this->data
            )
        ) : '';
    }
}
