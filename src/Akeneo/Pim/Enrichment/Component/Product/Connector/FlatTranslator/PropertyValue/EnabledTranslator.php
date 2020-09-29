<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator\PropertyValue;

use Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator\FlatTranslatorInterface;
use Akeneo\Tool\Component\Localization\LabelTranslatorInterface;

class EnabledTranslator implements FlatPropertyValueTranslatorInterface
{
    /** @var LabelTranslatorInterface */
    private $labelTranslator;

    public function __construct(LabelTranslatorInterface $labelTranslator)
    {
        $this->labelTranslator = $labelTranslator;
    }

    public function supports(string $columnName): bool
    {
        return 'enabled' === $columnName;
    }

    public function translate(array $values, string $locale, string $scope): array
    {
        $result = [];
        $trueLocalized = $this->labelTranslator->translate(
            'pim_common.yes',
            $locale,
            sprintf(FlatTranslatorInterface::FALLBACK_PATTERN, 'yes')
        );
        $falseLocalized = $this->labelTranslator->translate(
            'pim_common.no',
            $locale,
            sprintf(FlatTranslatorInterface::FALLBACK_PATTERN, 'no')
        );

        foreach ($values as $valueIndex => $value) {
            $result[$valueIndex] = $value ? $trueLocalized : $falseLocalized;
        }

        return $result;
    }
}
