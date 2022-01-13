<?php

namespace Akeneo\AssetManager\Infrastructure\Persistence\Sql;

use Akeneo\AssetManager\Domain\Query\Locale\FindActivatedLocalesInterface;

class InactiveLabelFilter
{
    private ?array $activatedLocalesCache = null;

    public function __construct(private FindActivatedLocalesInterface $findActivatedLocales)
    {
    }

    public function filter(array $labels): array
    {
        $activatedLocales = $this->getActivatedLocales();

        $filteredLabels = [];
        foreach ($labels as $localeCode => $label) {
            if (in_array($localeCode, $activatedLocales)) {
                $filteredLabels[$localeCode] = $label;
            }
        }

        return $filteredLabels;
    }

    private function getActivatedLocales()
    {
        if ($this->activatedLocalesCache === null) {
            $this->activatedLocalesCache = $this->findActivatedLocales->findAll();
        }

        return $this->activatedLocalesCache;
    }
}
