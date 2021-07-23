<?php

declare(strict_types=1);

namespace spec\AkeneoMeasureBundle\Provider;

use AkeneoMeasureBundle\Model\LabelCollection;
use AkeneoMeasureBundle\Model\MeasurementFamily;
use AkeneoMeasureBundle\Model\MeasurementFamilyCode;
use AkeneoMeasureBundle\Model\Operation;
use AkeneoMeasureBundle\Model\Unit;
use AkeneoMeasureBundle\Model\UnitCode;
use AkeneoMeasureBundle\Provider\LegacyMeasurementAdapter;
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
