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

namespace Akeneo\AssetManager\Infrastructure\Persistence\Channel;

use Akeneo\AssetManager\Domain\Model\LocaleIdentifierCollection;
use Akeneo\AssetManager\Domain\Query\Locale\FindActivatedLocalesByIdentifiersInterface;
use Akeneo\Channel\API\Query\FindLocales;

/**
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class FindActivatedLocalesByIdentifiers implements FindActivatedLocalesByIdentifiersInterface
{
    public function __construct(
        private FindLocales $findLocales
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function find(LocaleIdentifierCollection $localeIdentifiers): LocaleIdentifierCollection
    {
        $activatedLocaleCodes = [];
        if (!$localeIdentifiers->isEmpty()) {
            $activatedLocaleCodes = $this->fetchActivatedLocaleCodesFromIdentifiers($localeIdentifiers);
        }

        return LocaleIdentifierCollection::fromNormalized($activatedLocaleCodes);
    }

    /**
     * @return string[]
     */
    private function fetchActivatedLocaleCodesFromIdentifiers(LocaleIdentifierCollection $localeIdentifiers): array
    {
        $activatedLocales = $this->findLocales->findAllActivated();
        $localeIdentifiersAsString = array_map('strtolower', $localeIdentifiers->normalize());
        $activatedLocaleCodes = [];

        foreach ($activatedLocales as $activatedLocale) {
            if (in_array(strtolower($activatedLocale->getCode()), $localeIdentifiersAsString)) {
                $activatedLocaleCodes[] = $activatedLocale->getCode();
            }
        }

        return $activatedLocaleCodes;
    }
}
