<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\MeasureBundle\tests\Integration\PublicApi;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Bundle\MeasureBundle\ServiceApi\SqlGetUnitTranslations;

final class SqlGetUnitTranslationsIntegration extends TestCase
{
    public function test_it_gets_unit_translations_by_measurement_family_code_and_locale(): void
    {
        $query = $this->getQuery();

        $expected = [
            'MICROGRAM' => 'Microgramme',
            'MILLIGRAM' => 'Milligramme',
            'GRAM' => 'Gramme',
            'KILOGRAM' => 'Kilogramme',
            'TON' => 'Tonne',
            'GRAIN' => 'Grain',
            'DENIER' => 'Denier',
            'ONCE' => 'Once française',
            'MARC' => 'Marc',
            'LIVRE' => 'Livre française',
            'OUNCE' => 'Once',
            'POUND' => 'Livre',
        ];
        $actual = $query->byMeasurementFamilyCodeAndLocale('Weight', 'fr_FR');

        $this->assertEqualsCanonicalizing($expected, $actual);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function getQuery(): SqlGetUnitTranslations
    {
        return $this->get('akeneo_measurement.service_api.get_unit_translations');
    }
}
