<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Subscriber\Locale;

use Akeneo\Channel\Component\Model\LocaleInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Application\Spellcheck\SupportedLocaleValidator;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write\TextCheckerDictionaryWord;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\DictionaryWord;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Repository\TextCheckerDictionaryRepository;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

final class AddDefaultDictionaryWordsSubscriber implements EventSubscriberInterface
{
    private FeatureFlag $dataQualityInsightsFeature;

    private const DEFAULT_WORDS = [
        'sku', 'upc', 'asin', 'ean', 'mpn', 'gtin', 'jan', 'isbn', 'erp',
        'xxs', 'xs', 's', 'm', 'l', 'xl', 'xxl', 'xxxl',
    ];

    private TextCheckerDictionaryRepository $textCheckerDictionaryRepository;

    private SupportedLocaleValidator $supportedLocaleValidator;

    public function __construct(
        FeatureFlag $dataQualityInsightsFeature,
        TextCheckerDictionaryRepository $textCheckerDictionaryRepository,
        SupportedLocaleValidator $supportedLocaleValidator
    ) {
        $this->dataQualityInsightsFeature = $dataQualityInsightsFeature;
        $this->textCheckerDictionaryRepository = $textCheckerDictionaryRepository;
        $this->supportedLocaleValidator = $supportedLocaleValidator;
    }

    public static function getSubscribedEvents()
    {
        return [
            StorageEvents::POST_SAVE => ['onSaveLocale'],
        ];
    }

    public function onSaveLocale(GenericEvent $event): void
    {
        $locale = $event->getSubject();

        if (!$locale instanceof LocaleInterface || !$locale->isActivated() || !$this->dataQualityInsightsFeature->isEnabled()) {
            return;
        }

        $localeCode = new LocaleCode($locale->getCode());

        if (!$this->supportedLocaleValidator->isSupported($localeCode)) {
            return;
        }

        // If the dictionary is not empty for the locale, we consider that the locale has already been activated.
        if (!$this->textCheckerDictionaryRepository->isEmptyForLocale($localeCode)) {
            return;
        }

        $defaultWords = array_map(fn (string $word) => new TextCheckerDictionaryWord($localeCode, new DictionaryWord($word)), self::DEFAULT_WORDS);

        $this->textCheckerDictionaryRepository->saveAll($defaultWords);
    }
}
