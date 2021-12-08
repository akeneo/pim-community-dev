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

namespace Akeneo\ReferenceEntity\Domain\Query\Record\Connector;

use Akeneo\ReferenceEntity\Domain\Model\ChannelIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\LocaleIdentifierCollection;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;

/**
 * @author    Elodie Raposo <elodie.raposo@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class ConnectorRecord
{
    /**
     * @param array<string, mixed[]> $normalizedValues
     */
    public function __construct(private RecordCode $code, private array $normalizedValues)
    {
    }

    public function normalize(): array
    {
        return [
            'code' => $this->code->normalize(),
            'values' => empty($this->normalizedValues) ? (object) []: $this->normalizedValues,
        ];
    }

    public function getRecordWithValuesFilteredOnChannel(ChannelIdentifier $channelIdentifier): ConnectorRecord
    {
        $filteredValues = [];
        foreach ($this->normalizedValues as $key => $normalizedValue) {
            $filteredValue = array_values(array_filter($normalizedValue, static fn (array $value) => null === $value['channel']
                || $channelIdentifier->equals(ChannelIdentifier::fromCode($value['channel']))));

            if (!empty($filteredValue)) {
                $filteredValues[$key] = $filteredValue;
            }
        }

        return new self($this->code, $filteredValues);
    }

    public function getRecordWithValuesFilteredOnLocales(LocaleIdentifierCollection $localeIdentifiers): ConnectorRecord
    {
        $localeCodes = $localeIdentifiers->normalize();

        $filteredValues = array_map(static fn ($normalizedValue) => array_values(array_filter($normalizedValue, static fn ($value) => null === $value['locale']
            || in_array($value['locale'], $localeCodes))), $this->normalizedValues);

        $filteredValues = array_filter($filteredValues, static fn ($filteredValue) => !empty($filteredValue));

        return new self($this->code, $filteredValues);
    }
}
