<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\TableAttribute\Infrastructure\Connector\FlatTranslator;

use Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator\FlatTranslatorInterface;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Channel\GetChannelTranslations;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Tool\Component\Localization\LanguageTranslator;

class AttributeTranslator
{
    private AttributeRepositoryInterface $attributeRepository;
    private LanguageTranslator $languageTranslator;
    private GetChannelTranslations $getChannelTranslations;
    private array $localeTranslationCache = [];
    private array $channelTranslationCache = [];

    public function __construct(
        AttributeRepositoryInterface $attributeRepository,
        LanguageTranslator $languageTranslator,
        GetChannelTranslations $getChannelTranslations
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->languageTranslator = $languageTranslator;
        $this->getChannelTranslations = $getChannelTranslations;
    }

    public function translate(string $column, string $localeCode): string
    {
        $attributeParts = \explode('-', $column);
        $attributeCode = $attributeParts[0];

        $attribute = $this->attributeRepository->findOneByIdentifier($attributeCode);
        $translation = $attribute->getTranslation($localeCode);
        $attributeLabel = null !== $translation && null !== $translation->getLabel()
            ? $translation->getLabel()
            : \sprintf(FlatTranslatorInterface::FALLBACK_PATTERN, $attributeCode)
        ;

        if ($attribute->isLocalizable() && $attribute->isScopable()) {
            return \sprintf(
                '%s (%s, %s)',
                $attributeLabel,
                $this->getLocaleLabel($attributeParts[1], $localeCode),
                $this->getScopeLabel($attributeParts[2], $localeCode)
            );
        } elseif ($attribute->isLocalizable()) {
            return \sprintf(
                '%s (%s)',
                $attributeLabel,
                $this->getLocaleLabel($attributeParts[1], $localeCode),
            );
        } elseif ($attribute->isScopable()) {
            return \sprintf(
                '%s (%s)',
                $attributeLabel,
                $this->getScopeLabel($attributeParts[1], $localeCode)
            );
        }

        return $attributeLabel;
    }

    private function getLocaleLabel(string $locale, string $localeCode): string
    {
        if (!\in_array($locale, $this->localeTranslationCache)) {
            $this->localeTranslationCache[$locale] = $this->languageTranslator->translate(
                $locale,
                $localeCode,
                \sprintf(FlatTranslatorInterface::FALLBACK_PATTERN, $locale)
            );
        }

        return $this->localeTranslationCache[$locale];
    }

    private function getScopeLabel(string $channelCode, string $localeCode): string
    {
        if (!\in_array($localeCode, $this->channelTranslationCache)) {
            $this->channelTranslationCache[$localeCode] = $this->getChannelTranslations->byLocale($localeCode);
        }

        return $this->channelTranslationCache[$localeCode][$channelCode] ??
            \sprintf(FlatTranslatorInterface::FALLBACK_PATTERN, $channelCode);
    }
}
