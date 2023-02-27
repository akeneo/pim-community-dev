<?php

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Measurement;

use Akeneo\Catalogs\Application\Mapping\Measurement\MeasurementConverter;
use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;

class MeasurementConverterTest extends IntegrationTestCase
{
    private ?MeasurementConverter $measurementConverter;

    protected function setUp(): void
    {
        parent::setUp();

        $this->measurementConverter = self::getContainer()->get(MeasurementConverter::class);

        $this->purgeDataAndLoadMinimalCatalog();
    }

    public function dataToConvertFromMilligramProvider(): array
    {
        return [
            'MICROGRAM' => ['MICROGRAM', 12000000],
            'MILLIGRAM' => ['MILLIGRAM', 12000],
            'GRAM' => ['GRAM', 12],
            'KILOGRAM' => ['KILOGRAM', 0.012],
            'TON' => ['TON', 0.000012],
            'OUNCE' => ['OUNCE', 0.423287543394],
            'POUND' => ['POUND', 0.026455471462],
        ];
    }

    /**
     * @dataProvider dataToConvertFromMilligramProvider
     */
    public function testItConvertsFromMilligram(string $targetUnit, int|float $expectedValue): void
    {
        $result = $this->measurementConverter->convert('weight', $targetUnit, 'MILLIGRAM', 12000);
        $this->assertEquals($expectedValue, $result);
    }

    public function dataToConvertFromOunceProvider(): array
    {
        return [
            'MICROGRAM' => ['MICROGRAM', 340194277.5],
            'MILLIGRAM' => ['MILLIGRAM', 340194.2775],
            'GRAM' => ['GRAM', 340.1942775],
            'KILOGRAM' => ['KILOGRAM', 0.3401942775],
            'TON' => ['TON', 0.000340194277],
            'OUNCE' => ['OUNCE', 12],
            'POUND' => ['POUND', 0.75],
        ];
    }

    /**
     * @dataProvider dataToConvertFromOunceProvider
     */
    public function testItConvertsFromOnce(string $targetUnit, int|float $expectedValue): void
    {
        $result = $this->measurementConverter->convert('weight', $targetUnit, 'OUNCE', 12);
        $this->assertEquals($expectedValue, $result);
    }
}
