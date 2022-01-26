<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Test\Pim\TableAttribute\Integration\TableConfiguration\Query;

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\MeasurementColumn;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Query\IsMeasurementFamilyLinkedToATableColumn;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\SelectColumn;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Test\Pim\TableAttribute\Helper\EntityBuilderTrait;
use Akeneo\Tool\Bundle\MeasureBundle\Application\CreateMeasurementFamily\CreateMeasurementFamilyCommand;
use Akeneo\Tool\Bundle\MeasureBundle\Application\CreateMeasurementFamily\CreateMeasurementFamilyHandler;
use Akeneo\Tool\Bundle\MeasureBundle\Model\LabelCollection;
use Akeneo\Tool\Bundle\MeasureBundle\Model\Operation;
use Akeneo\Tool\Bundle\MeasureBundle\Model\Unit;
use Akeneo\Tool\Bundle\MeasureBundle\Model\UnitCode;

class SqlIsMeasurementFamilyLinkedToATableColumnIntegration extends TestCase
{
    use EntityBuilderTrait;
    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $second = Unit::create(
            UnitCode::fromString('second_unit'),
            LabelCollection::fromArray(['en_US' => 'Second Unit', 'fr_FR' => 'Unité Seconde']),
            [Operation::create('mul', '1')],
            's'
        )->normalize();

        $meter = Unit::create(
            UnitCode::fromString('meter_unit'),
            LabelCollection::fromArray(['en_US' => 'Meter Unit', 'fr_FR' => 'Unité Mètre']),
            [Operation::create('mul', '1')],
            'm'
        )->normalize();

        $this->createMeasurementFamily('duration_measurement', $second['code'], [$second]);
        $this->createMeasurementFamily('distance_measurement', $meter['code'], [$meter]);

        $this->createAttribute(
            [
                'code' => 'packaging',
                'type' => AttributeTypes::TABLE,
                'group' => 'other',
                'localizable' => false,
                'scopable' => false,
                'table_configuration' => [
                    ['data_type' => SelectColumn::DATATYPE, 'code' => 'process', 'options' => [
                        ['code' => 'sewing', 'labels' => ['en_US' => 'Sewing']],
                        ['code' => 'gluing', 'labels' => ['en_US' => 'Gluing']],
                    ]],
                    [
                        'data_type' => MeasurementColumn::DATATYPE,
                        'code' => 'duration_column',
                        'measurement_family_code' => 'duration_measurement',
                        'measurement_default_unit_code' => $second['code']
                    ],
                ]
            ]
        );
    }

    /** @test */
    public function itSaysIfAMeasurementColumnIsLinkedToATableColumn(): void
    {
        $service = $this->get(IsMeasurementFamilyLinkedToATableColumn::class);

        self::assertFalse($service->forCode('distance_measurement'));
        self::assertFalse($service->forCode('unkown_measurement_family'));
        self::assertTrue($service->forCode('duration_measurement'));
        self::assertTrue($service->forCode('DURATIOn_measurement'));
    }

    private function createMeasurementFamily(string $familyCode, string $defaultUnitCode, array $units)
    {
        $createCommand = new CreateMeasurementFamilyCommand();
        $createCommand->code = $familyCode;
        $createCommand->standardUnitCode = $defaultUnitCode;
        $createCommand->units = $units;
        $createCommand->labels = [];

        $violations = $this->get('validator')->validate($createCommand);
        self::assertCount(0, $violations, (string)$violations);

        /** @var CreateMeasurementFamilyHandler $handler */
        $handler = $this->get('akeneo_measure.application.create_measurement_family_handler');
        $handler->handle($createCommand);
    }
}
