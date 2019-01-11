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
use Akeneo\ReferenceEntity\Domain\Model\Image;
use Akeneo\ReferenceEntity\Domain\Model\LabelCollection;
use Akeneo\ReferenceEntity\Domain\Model\LocaleIdentifierCollection;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;

/**
 * @author    Elodie Raposo <elodie.raposo@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class ConnectorRecord
{
    /** @var RecordCode */
    private $code;

    /** @var LabelCollection */
    private $labelCollection;

    /** @var Image */
    private $image;

    /** @var array */
    private $normalizedValues;

    public function __construct(
        RecordCode $code,
        LabelCollection $labelCollection,
        Image $image,
        array $normalizedValues
    ) {
        $this->code = $code;
        $this->labelCollection = $labelCollection;
        $this->image = $image;
        $this->normalizedValues = $normalizedValues;
    }

    public function normalize(): array
    {
        return [
            'code' => $this->code->normalize(),
            'labels' => $this->labelCollection->normalize(),
            'values' => empty($this->normalizedValues) ? (object) []: $this->normalizedValues,
            'main_image' => $this->image->isEmpty() ? null : $this->image->getKey()
        ];
    }

    public function getRecordWithValuesFilteredOnChannel(ChannelIdentifier $channelIdentifier): ConnectorRecord
    {
        $filteredValues = [];
        foreach ($this->normalizedValues as $key => $normalizedValue) {
            $filteredValue = array_values(array_filter($normalizedValue, function ($value) use ($channelIdentifier) {
                return null === $value['channel']
                    || $channelIdentifier->equals(ChannelIdentifier::fromCode($value['channel']));
            }));

            if (!empty($filteredValue)) {
                $filteredValues[$key] = $filteredValue;
            }
        }

        return new self(
            $this->code,
            $this->labelCollection,
            $this->image,
            $filteredValues
        );
    }

    public function getRecordWithValuesAndLabelsFilteredOnLocales(LocaleIdentifierCollection $localeIdentifiers): ConnectorRecord
    {
        $localeCodes = $localeIdentifiers->normalize();

        $filteredValues = array_map(function ($normalizedValue) use ($localeCodes) {
            return array_values(array_filter($normalizedValue, function ($value) use ($localeCodes) {
                return null === $value['locale']
                    || in_array($value['locale'], $localeCodes);
            }));
        }, $this->normalizedValues);

        $filteredValues = array_filter($filteredValues, function ($filteredValue) {
            return !empty($filteredValue);
        });

        return new self(
            $this->code,
            $this->labelCollection->filterByLocaleIdentifiers($localeIdentifiers),
            $this->image,
            $filteredValues
        );
    }
}
