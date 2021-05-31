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

namespace Akeneo\AssetManager\Infrastructure\Persistence\Cache\Locale;

use Akeneo\AssetManager\Domain\Model\LocaleIdentifier;
use Akeneo\AssetManager\Domain\Model\LocaleIdentifierCollection;
use Akeneo\AssetManager\Domain\Query\Locale\FindActivatedLocalesByIdentifiersInterface;

/**
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class CacheFindActivatedLocalesByIdentifiers implements FindActivatedLocalesByIdentifiersInterface
{
    private FindActivatedLocalesByIdentifiersInterface $findActivatedLocalesByIdentifiers;

    /** @var LocaleIdentifier[] */
    private array $localesCache;

    public function __construct(FindActivatedLocalesByIdentifiersInterface $findActivatedLocalesByIdentifiers)
    {
        $this->findActivatedLocalesByIdentifiers = $findActivatedLocalesByIdentifiers;
        $this->localesCache = [];
    }

    public function find(LocaleIdentifierCollection $localeIdentifiers): LocaleIdentifierCollection
    {
        $requestedLocales = array_flip($localeIdentifiers->normalize());
        $missingLocalesInCache = array_keys(array_diff_key($requestedLocales, $this->localesCache));

        if (!empty($missingLocalesInCache)) {
            $this->loadLocalesInCache($missingLocalesInCache);
        }

        $localesInCache = array_intersect_key($this->localesCache, $requestedLocales);
        $activatedLocales = array_values(array_filter($localesInCache, fn ($localeIdentifier) => null !== $localeIdentifier));

        return new LocaleIdentifierCollection($activatedLocales);
    }

    private function loadLocalesInCache(array $locales): void
    {
        foreach ($locales as $locale) {
            $this->localesCache[$locale] = null;
        }

        $activatedLocales = $this->findActivatedLocalesByIdentifiers->find(LocaleIdentifierCollection::fromNormalized($locales));
        foreach ($activatedLocales as $activatedLocale) {
            $this->localesCache[$activatedLocale->normalize()] = $activatedLocale;
        }
    }
}
