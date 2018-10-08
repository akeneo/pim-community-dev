<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Infrastructure\Search\Elasticsearch;

use Akeneo\ReferenceEntity\Domain\Model\ChannelIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\LocaleIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Record\Record;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\ChannelReference;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\LocaleReference;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\TextData;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\Value;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\ValueCollection;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class SearchMatrixNormalizer
{
    /** @var SqlFindActivatedLocalesPerChannels */
    private $findActivatedLocalesPerChannels;

    public function __construct(SqlFindActivatedLocalesPerChannels $findActivatedLocalesPerChannels)
    {
        $this->findActivatedLocalesPerChannels = $findActivatedLocalesPerChannels;
    }

    public function generate(Record $record): array
    {
        $localesPerChannels = ($this->findActivatedLocalesPerChannels)();

        $matrix = [];
        $labels = $record->normalize()['labels'];
        foreach ($localesPerChannels as $channelCode => $localeCodes) {
            foreach ($localeCodes as $localeCode) {
                $recordProperties = (string) $record->getCode();
                $recordProperties .= $this->appendLabel($localeCode, $labels);
                $recordProperties .= $this->appendValue($record, $channelCode, $localeCode);

                $matrix[$channelCode][$localeCode] = $recordProperties;
            }
        }

        return $matrix;
    }

    private function appendLabel($localeCode, $labels): string
    {
        if (array_key_exists($localeCode, $labels)) {
            return ' ' . $labels[$localeCode];
        }

        return '';
    }

    private function appendValue(Record $record, string $channelCode, string $localeCode): string
    {
        $textValue = '';
        $values = $this->getValuesToIndex($record, $channelCode, $localeCode);
        /** @var Value $value */
        foreach ($values as $value) {
            $textValue = ' ' . $value->getData()->normalize();
        }

        return $textValue;
    }

    /**
     * Retrieve all values for channel and locale
     */
    private function getValuesToIndex(Record $record, string $channelCode, string $localeCode): ValueCollection
    {
        $values = $record->filterValues(function (Value $value) use ($channelCode, $localeCode) {
            $isText = $value->getData() instanceof TextData;
            $notEmpty = !$value->isEmpty();

            $isNonScopableNonLocalizable = !$value->hasChannel() && !$value->hasLocale();
            $hasChannel = $value->hasChannel() && $value->getChannelReference()->equals(ChannelReference::fromChannelIdentifier(ChannelIdentifier::fromCode($channelCode)));
            $hasLocale = $value->hasLocale() && $value->getLocaleReference()->equals(LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode($localeCode)));

            $nonLocalizableAndHasChannel = !$value->hasLocale() && $hasChannel;
            $nonScopableAndHasLocale = !$value->hasChannel() && $hasLocale;

            return $isText && $notEmpty && (
                    $isNonScopableNonLocalizable ||
                    ($hasChannel && $hasLocale) ||
                    $nonLocalizableAndHasChannel ||
                    $nonScopableAndHasLocale
                );
        });

        return $values;
    }
}
