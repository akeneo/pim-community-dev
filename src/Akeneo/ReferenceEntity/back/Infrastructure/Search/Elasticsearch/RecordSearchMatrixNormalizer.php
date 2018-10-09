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
class RecordSearchMatrixNormalizer
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
                $recordProperties .= $this->getLabel($localeCode, $labels);
                $recordProperties .= $this->getValue(
                    $record,
                    ChannelReference::fromChannelIdentifier(ChannelIdentifier::fromCode($channelCode)),
                    LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode($localeCode))
                );

                $matrix[$channelCode][$localeCode] = $recordProperties;
            }
        }

        return $matrix;
    }

    private function getLabel($localeCode, $labels): string
    {
        if (array_key_exists($localeCode, $labels)) {
            return ' ' . $labels[$localeCode];
        }

        return '';
    }

    private function getValue(Record $record, ChannelReference $channel, LocaleReference $locale): string
    {
        $textValue = '';
        $values = $this->getValuesToIndex($record, $channel, $locale);
        /** @var Value $value */
        foreach ($values as $value) {
            $textValue = ' ' . $value->getData()->normalize();
        }

        return $textValue;
    }

    /**
     * Retrieve all values for channel and locale
     */
    private function getValuesToIndex(Record $record, ChannelReference $channel, LocaleReference $locale): ValueCollection
    {
        $values = $record->filterValues(function (Value $value) use ($channel, $locale) {
            $isText = $value->getData() instanceof TextData;
            $notEmpty = !$value->isEmpty();
            $matchChannel = $value->getChannelReference()->isEmpty() || $value->getChannelReference()->equals($channel);
            $matchLocale = $value->getLocaleReference()->isEmpty() || $value->getLocaleReference()->equals($locale);

            return $isText && $notEmpty && $matchChannel && $matchLocale;
        });

        return $values;
    }
}
