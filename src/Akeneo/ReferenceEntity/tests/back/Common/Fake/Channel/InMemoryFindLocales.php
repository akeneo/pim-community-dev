<?php

namespace Akeneo\ReferenceEntity\Common\Fake\Channel;

use Akeneo\Channel\API\Query\FindLocales;
use Akeneo\Channel\API\Query\Locale;

class InMemoryFindLocales implements FindLocales
{
    private array $locales;

    public function __construct()
    {
        $this->locales = [
            'en_US' => new Locale('en_US', true),
            'fr_FR' => new Locale('fr_FR', true),
            'de_DE' => new Locale('de_DE', true),
        ];
    }

    public function find(string $localeCode): ?Locale
    {
        return $this->locales[$localeCode] ?? null;
    }

    public function findAllActivated(): array
    {
        return array_values($this->locales);
    }
}
