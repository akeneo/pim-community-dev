<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\MeasureBundle\tests\Integration\PublicApi;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Bundle\MeasureBundle\ServiceApi\SqlGetUnit;
use Akeneo\Tool\Bundle\MeasureBundle\ServiceApi\Unit;

final class SqlGetUnitIntegration extends TestCase
{
    public function test_it_gets_unit_by_measurement_family_code_and_unit_code(): void
    {
        $query = $this->getQuery();

        $expectedUnit = new Unit();
        $expectedUnit->code = 'MICROGRAM';
        $expectedUnit->labels = [
            'en_US' => 'Microgram',
            'fr_FR' => 'Microgramme',
        ];
        $expectedUnit->symbol = 'μg';
        $expectedUnit->convertFromStandard = [
            [
                'value' => '0.000000001',
                'operator' => 'mul',
            ]
        ];

        $this->assertEqualsCanonicalizing(
            $expectedUnit,
            $query->byMeasurementFamilyCodeAndUnitCode('Weight', 'MICROGRAM')
        );
    }

    public function test_it_throws_an_exception_when_unit_is_not_found(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Unit code BAR with family code Foo was not found');

        $this->getQuery()->byMeasurementFamilyCodeAndUnitCode('Foo', 'BAR');
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function getQuery(): SqlGetUnit
    {
        return $this->get('akeneo_measurement.service_api.get_unit');
    }
}
