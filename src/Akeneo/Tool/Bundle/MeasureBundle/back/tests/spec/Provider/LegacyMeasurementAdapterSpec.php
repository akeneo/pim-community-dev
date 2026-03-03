<?php

declare(strict_types=1);

namespace spec\Akeneo\Tool\Bundle\MeasureBundle\Provider;

use Akeneo\Tool\Bundle\MeasureBundle\Model\LabelCollection;
use Akeneo\Tool\Bundle\MeasureBundle\Model\MeasurementFamily;
use Akeneo\Tool\Bundle\MeasureBundle\Model\MeasurementFamilyCode;
use Akeneo\Tool\Bundle\MeasureBundle\Model\Operation;
use Akeneo\Tool\Bundle\MeasureBundle\Model\Unit;
use Akeneo\Tool\Bundle\MeasureBundle\Model\UnitCode;
use Akeneo\Tool\Bundle\MeasureBundle\Provider\LegacyMeasurementAdapter;
use PhpSpec\ObjectBehavior;

/**
 * @author    Valentin Dijkstra <valentin.dijkstra@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 */
class LegacyMeasurementAdapterSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType(LegacyMeasurementAdapter::class);
    }

    public function it_adapts_a_measurement_family_to_legacy_measurements()
    {
        $measurementFamily = MeasurementFamily::create(
            MeasurementFamilyCode::fromString('Area'),
            LabelCollection::fromArray(['en_US' => 'Area', 'fr_FR' => 'Surface']),
            UnitCode::fromString('SQUARE_MILLIMETER'),
            [
                Unit::create(
                    UnitCode::fromString('SQUARE_MILLIMETER'),
                    LabelCollection::fromArray(['en_US' => 'Square millimeter', 'fr_FR' => 'Millimètre carré']),
                    [Operation::create('mul', '1')],
                    'mm²',
                ),
                Unit::create(
                    UnitCode::fromString('SQUARE_CENTIMETER'),
                    LabelCollection::fromArray(['en_US' => 'Square centimeter', 'fr_FR' => 'Centimètre carré']),
                    [Operation::create('mul', '0.0001'), Operation::create('add', '4')],
                    'cm²',
                )
            ]
        );

        $this->adapts($measurementFamily)->shouldReturn([
            'Area' => [
                'standard' => 'SQUARE_MILLIMETER',
                'units' => [
                    'SQUARE_MILLIMETER' => [
                        'convert' => [
                            ['mul' => '1'],
                        ],
                        'symbol' => 'mm²',
                    ],
                    'SQUARE_CENTIMETER' => [
                        'convert' => [
                            ['mul' => '0.0001'],
                            ['add' => '4'],
                        ],
                        'symbol' => 'cm²',
                    ],
                ]
            ]
        ]);
    }
}
