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
use Webmozart\Assert\Assert;

class AttributeTranslator
{
    /** @var array<string, array<string, string>> */
    private array $localeTranslationCache = [];
    /** @var array<string, array<string, string>> */
    private array $channelTranslationCache = [];

    public function __construct(
        private AttributeRepositoryInterface $attributeRepository,
        private LanguageTranslator $languageTranslator,
        private GetChannelTranslations $getChannelTranslations
    ) {
    }

    /**
     * For example, it translates "nutrition-en_US-ecommerce" into "Nutrition (English US, Ecommerce)"
     */
    public function translate(string $attributeLocaleScope, string $displayLocale): string
    {
        $attributeParts = \explode('-', $attributeLocaleScope);
        $attributeCode = $attributeParts[0];

        $attribute = $this->attributeRepository->findOneByIdentifier($attributeCode);
        $translation = $attribute->getTranslation($displayLocale);
        $attributeLabel = null !== $translation && null !== $translation->getLabel()
            ? $translation->getLabel()
            : \sprintf(FlatTranslatorInterface::FALLBACK_PATTERN, $attributeCode)
        ;

        if ($attribute->isLocalizable() && $attribute->isScopable()) {
            Assert::count($attributeParts, 3);
            return \sprintf(
                '%s (%s, %s)',
                $attributeLabel,
                $this->getLocaleLabel($attributeParts[1], $displayLocale),
                $this->getScopeLabel($attributeParts[2], $displayLocale)
            );
        } elseif ($attribute->isLocalizable()) {
            Assert::count($attributeParts, 2);
            return \sprintf(
                '%s (%s)',
                $attributeLabel,
                $this->getLocaleLabel($attributeParts[1], $displayLocale),
            );
        } elseif ($attribute->isScopable()) {
            Assert::count($attributeParts, 2);
            return \sprintf(
                '%s (%s)',
                $attributeLabel,
                $this->getScopeLabel($attributeParts[1], $displayLocale)
            );
        }

        return $attributeLabel;
    }

    private function getLocaleLabel(string $locale, string $localeCode): string
    {
        if (!\in_array($localeCode, $this->localeTranslationCache)
            || !\in_array($locale, $this->localeTranslationCache[$localeCode])
        ) {
            $this->localeTranslationCache[$localeCode][$locale] = $this->languageTranslator->translate(
                $locale,
                $localeCode,
                \sprintf(FlatTranslatorInterface::FALLBACK_PATTERN, $locale)
            );
        }

        return $this->localeTranslationCache[$localeCode][$locale];
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
