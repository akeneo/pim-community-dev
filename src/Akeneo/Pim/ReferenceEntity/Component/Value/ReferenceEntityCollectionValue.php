<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\ReferenceEntity\Component\Value;

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
    public function isEqual(ValueInterface $value): bool
    {
        return $this->getData() === $value->getData() &&
            $this->scopeCode === $value->getScopeCode() &&
            $this->localeCode === $value->getLocaleCode();
    }

    /**
     * {@inheritdoc}
     */
    public function __toString(): string
    {
        return null !== $this->data ? implode(array_map(function (RecordCode $recordCode) {
            return $recordCode->__toString();
        }, $this->data), ', ') : '';
    }
}
