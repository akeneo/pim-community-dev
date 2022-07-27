<?php

namespace Akeneo\Platform\TailoredImport\Test\Integration\FakePublicApi;

use Akeneo\Pim\Structure\Family\ServiceAPI\Query\FamilyQuery;
use Akeneo\Pim\Structure\Family\ServiceAPI\Query\FamilyWithLabels;
use Akeneo\Pim\Structure\Family\ServiceAPI\Query\FindFamiliesWithLabels;

class FakeFindFamiliesWithLabels implements FindFamiliesWithLabels
{
    public function fromQuery(FamilyQuery $query): array
    {
        $families = $this->getFamilies();

        if ($query->search === null) {
            return $families;
        }

        return array_values(array_filter($families, function (FamilyWithLabels $family) use ($query) {
            $labels = $family->getLabels();
            $label = array_key_exists($query->search->labelLocale, $labels)
                ? $family->getLabels()[$query->search->labelLocale]
                : '';

            return false !== str_contains($label, $query->search->value)
                || false !== str_contains($family->getCode(), $query->search->value);
        }));
    }

    private function getFamilies(): array
    {
        return [
            new FamilyWithLabels('tshirt', [
                'en_US' => 'T-shirt',
                'fr_FR' => 'T-shirt',
            ]),
            new FamilyWithLabels('bed', [
                'en_US' => 'Bed',
                'fr_FR' => 'Lit',
            ]),
            new FamilyWithLabels('car', [
                'en_US' => 'Car',
                'de_DE' => 'Auto',
            ]),
            new FamilyWithLabels('accessories', [
                'en_US' => 'Accessories',
                'fr_FR' => 'Accessoires',
            ]),
            new FamilyWithLabels('magic_cards', [
                'en_US' => 'Magic cards',
                'fr_FR' => 'Cartes Magic',
            ]),
        ];
    }
}
