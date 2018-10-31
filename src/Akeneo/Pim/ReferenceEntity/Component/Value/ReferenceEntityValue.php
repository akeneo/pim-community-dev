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
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\ReferenceEntity\Domain\Model\Record\Record;

/**
 * Product value for a reference entity
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
class ReferenceEntityValue extends AbstractValue implements ReferenceEntityValueInterface
{
    /** @var Record|null */
    protected $record;

    /**
     * @param AttributeInterface $attribute
     * @param string             $channel
     * @param string             $locale
     * @param Record|null        $record
     */
    public function __construct(AttributeInterface $attribute, $channel, $locale, $record = null)
    {
        $this->setAttribute($attribute);
        $this->setScope($channel);
        $this->setLocale($locale);

        $this->record = $record;
    }

    /**
     * {@inheritdoc}
     */
    public function getData(): ?Record
    {
        return $this->record;
    }

    /**
     * {@inheritdoc}
     */
    public function isEqual(ValueInterface $value): bool
    {
        if (null === $this->getData() || null === $value->getData()) {
            $areEqual = ($this->getData() === $value->getData());
        } else {
            $areEqual = $this->getData()->equals($value->getData());
        }

        return $areEqual
            && $this->scope === $value->getScope()
            && $this->locale === $value->getLocale();
    }

    /**
     * {@inheritdoc}
     */
    public function __toString(): string
    {
        return (null !== $this->record) ? (string) $this->record->getIdentifier() : '';
    }
}
