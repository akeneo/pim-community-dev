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

use Akeneo\ReferenceEntity\Domain\Model\Record\Record;
use Akeneo\Pim\Enrichment\Component\Product\Model\AbstractValue;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;

/**
 * Product value for enriched entity
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class ReferenceEntityCollectionValue extends AbstractValue implements ReferenceEntityCollectionValueInterface
{
    /** @var Record[] */
    protected $records;

    /**
     * @param AttributeInterface          $attribute
     * @param string                      $channel
     * @param string                      $locale
     * @param Record|null                 $records
     */
    public function __construct(AttributeInterface $attribute, $channel, $locale, $records = null)
    {
        $this->setAttribute($attribute);
        $this->setScope($channel);
        $this->setLocale($locale);

        $this->records = $records;
    }

    /**
     * {@inheritdoc}
     */
    public function getData(): array
    {
        return $this->records;
    }

    /**
     * {@inheritdoc}
     */
    public function isEqual(ValueInterface $value): bool
    {
        return $this->getData() === $value->getData() &&
            $this->scope === $value->getScope() &&
            $this->locale === $value->getLocale();
    }

    /**
     * {@inheritdoc}
     */
    public function __toString(): string
    {
        return null !== $this->records ? implode(array_map(function (Record $record) {
            return $record->getIdentifier()->__toString();
        }, $this->records), ', ') : '';
    }
}
