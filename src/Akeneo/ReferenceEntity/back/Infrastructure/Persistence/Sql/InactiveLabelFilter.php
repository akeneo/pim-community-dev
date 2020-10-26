<?php

namespace Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql;

use Akeneo\ReferenceEntity\Domain\Query\Locale\FindActivatedLocalesInterface;

class InactiveLabelFilter
{
    /** @var FindActivatedLocalesInterface */
    private $findActivatedLocales;

    /** @var array|null */
    private $activatedLocalesCache = null;

    public function __construct(FindActivatedLocalesInterface $findActivatedLocales)
    {
        $this->findActivatedLocales = $findActivatedLocales;
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
