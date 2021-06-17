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

namespace Akeneo\AssetManager\Domain\Query\Asset\Connector;

use Akeneo\AssetManager\Domain\Model\Asset\AssetCode;
use Akeneo\AssetManager\Domain\Model\ChannelIdentifier;
use Akeneo\AssetManager\Domain\Model\LocaleIdentifierCollection;

/**
 * @author    Elodie Raposo <elodie.raposo@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class ConnectorAsset
{
    private AssetCode $code;

    private array $normalizedValues;

    private \DateTimeImmutable $createdAt;

    private \DateTimeImmutable $updatedAt;

    public function __construct(
        AssetCode $code,
        array $normalizedValues,
        \DateTimeImmutable $createdAt,
        \DateTimeImmutable $updatedAt
    ) {
        $this->code = $code;
        $this->normalizedValues = $normalizedValues;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    public function normalize(): array
    {
        return [
            'code'   => $this->code->normalize(),
            'values' => empty($this->normalizedValues) ? (object) [] : $this->normalizedValues,
            'created' => $this->createdAt->format(\DateTimeInterface::ATOM),
            'updated' => $this->updatedAt->format(\DateTimeInterface::ATOM),
        ];
    }

    public function getAssetWithValuesFilteredOnChannel(ChannelIdentifier $channelIdentifier): ConnectorAsset
    {
        $filteredValues = [];
        foreach ($this->normalizedValues as $key => $normalizedValue) {
            $filteredValue = array_values(array_filter($normalizedValue, fn ($value) => null === $value['channel']
                || $channelIdentifier->equals(ChannelIdentifier::fromCode($value['channel']))));

            if (!empty($filteredValue)) {
                $filteredValues[$key] = $filteredValue;
            }
        }

        return new self($this->code, $filteredValues, $this->createdAt, $this->updatedAt);
    }

    public function getAssetWithValuesFilteredOnLocales(LocaleIdentifierCollection $localeIdentifiers): ConnectorAsset
    {
        $localeCodes = $localeIdentifiers->normalize();

        $filteredValues = array_map(fn ($normalizedValue) => array_values(array_filter($normalizedValue, fn ($value) => null === $value['locale']
            || in_array($value['locale'], $localeCodes))), $this->normalizedValues);

        $filteredValues = array_filter($filteredValues, fn ($filteredValue) => !empty($filteredValue));

        return new self($this->code, $filteredValues, $this->createdAt, $this->updatedAt);
    }
}
