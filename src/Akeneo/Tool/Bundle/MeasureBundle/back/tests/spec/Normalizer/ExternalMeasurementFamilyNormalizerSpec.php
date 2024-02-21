<?php

declare(strict_types=1);

namespace spec\Akeneo\Tool\Bundle\MeasureBundle\Normalizer;

use Akeneo\Tool\Bundle\MeasureBundle\Model\LabelCollection;
use Akeneo\Tool\Bundle\MeasureBundle\Model\MeasurementFamily;
use Akeneo\Tool\Bundle\MeasureBundle\Model\MeasurementFamilyCode;
use Akeneo\Tool\Bundle\MeasureBundle\Model\Operation;
use Akeneo\Tool\Bundle\MeasureBundle\Model\Unit;
use Akeneo\Tool\Bundle\MeasureBundle\Model\UnitCode;
use Akeneo\Tool\Bundle\MeasureBundle\Normalizer\ExternalMeasurementFamilyNormalizer;
use PhpSpec\ObjectBehavior;

/**
 * @author    Valentin Dijkstra <valentin.dijkstra@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 */
class ExternalMeasurementFamilyNormalizerSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType(ExternalMeasurementFamilyNormalizer::class);
    }

    public function it_normalizes_a_measurement_family_and_replaces_empty_arrays_by_empty_objects() {
        $aMeasurementFamily = MeasurementFamily::create(
            MeasurementFamilyCode::fromString('Area'),
            LabelCollection::fromArray([]),
            UnitCode::fromString('SQUARE_MILLIMETER'),
            [
                Unit::create(
                    UnitCode::fromString('SQUARE_MILLIMETER'),
                    LabelCollection::fromArray([]),
                    [Operation::create('mul', '1')],
                    'mm²',
                ),
                Unit::create(
                    UnitCode::fromString('SQUARE_CENTIMETER'),
                    LabelCollection::fromArray(['en_US' => 'Square centimeter', 'fr_FR' => 'Centimètre carré']),
                    [Operation::create('mul', '0.0001')],
                    'cm²',
                )
            ]
        );

        $this->normalize($aMeasurementFamily)->shouldBeLike([
            'code' => 'Area',
            'labels' => new \ArrayObject(),
            'standard_unit_code' => 'SQUARE_MILLIMETER',
            'units' => [
                'SQUARE_MILLIMETER' => [
                    'code' => 'SQUARE_MILLIMETER',
                    'labels' => new \ArrayObject(),
                    'convert_from_standard' => [
                        [
                            'operator' => 'mul',
                            'value' => '1'
                        ]
                    ],
                    'symbol' => 'mm²'
                ],
                'SQUARE_CENTIMETER' => [
                    'code' => 'SQUARE_CENTIMETER',
                    'labels' => [
                        'en_US' => 'Square centimeter',
                        'fr_FR' => 'Centimètre carré'
                    ],
                    'convert_from_standard' => [
                        [
                            'operator' => 'mul',
                            'value' => '0.0001'
                        ]
                    ],
                    'symbol' => 'cm²'
                ]
            ]
        ]);
    }
}
