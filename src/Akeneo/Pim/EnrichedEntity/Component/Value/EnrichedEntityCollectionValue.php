<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\EnrichedEntity\Component\Value;

use Akeneo\EnrichedEntity\Domain\Model\Record\Record;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Pim\Component\Catalog\Model\AbstractValue;

/**
 * Product value for enriched entity
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class EnrichedEntityCollectionValue extends AbstractValue implements EnrichedEntityCollectionValueInterface
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
    public function __toString(): string
    {
        return null !== $this->records ? implode(array_map(function (Record $record) {
            return (string) $record->getIdentifier();
        }, $this->records), ', ') : '';
    }
}
