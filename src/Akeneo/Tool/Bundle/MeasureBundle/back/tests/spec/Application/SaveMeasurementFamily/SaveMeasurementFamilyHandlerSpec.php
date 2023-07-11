<?php

declare(strict_types=1);

namespace spec\Akeneo\Tool\Bundle\MeasureBundle\Application\SaveMeasurementFamily;

use Akeneo\Tool\Bundle\MeasureBundle\Application\SaveMeasurementFamily\SaveMeasurementFamilyCommand;
use Akeneo\Tool\Bundle\MeasureBundle\Application\SaveMeasurementFamily\SaveMeasurementFamilyHandler;
use Akeneo\Tool\Bundle\MeasureBundle\Model\LabelCollection;
use Akeneo\Tool\Bundle\MeasureBundle\Model\MeasurementFamily;
use Akeneo\Tool\Bundle\MeasureBundle\Model\MeasurementFamilyCode;
use Akeneo\Tool\Bundle\MeasureBundle\Model\Operation;
use Akeneo\Tool\Bundle\MeasureBundle\Model\Unit;
use Akeneo\Tool\Bundle\MeasureBundle\Model\UnitCode;
use Akeneo\Tool\Bundle\MeasureBundle\Persistence\MeasurementFamilyRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\EventDispatcher\EventDispatcherInterface;
use Webmozart\Assert\Assert;

/**
 * @author    Valentin Dijkstra <valentin.dijkstra@akeneo.com>
 * @copyright 2020 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class SaveMeasurementFamilyHandlerSpec extends ObjectBehavior
{
    function let(
        MeasurementFamilyRepositoryInterface $measurementFamilyRepository,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->beConstructedWith($measurementFamilyRepository, $eventDispatcher);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(SaveMeasurementFamilyHandler::class);
    }

    function it_creates_and_saves_a_new_measurement_family(
        MeasurementFamilyRepositoryInterface $measurementFamilyRepository,
        SaveMeasurementFamilyCommand $saveMeasurementFamilyCommand
    ) {
        $saveMeasurementFamilyCommand->code = 'Area';
        $saveMeasurementFamilyCommand->labels = ['en_US' => 'Area', 'fr_FR' => 'Surface'];
        $saveMeasurementFamilyCommand->standardUnitCode = 'SQUARE_MILLIMETER';
        $saveMeasurementFamilyCommand->units = [
            [
                'code' => 'SQUARE_MILLIMETER',
                'labels' => ['en_US' => 'Square millimeter', 'fr_FR' => 'Millimètre carré'],
                'convert_from_standard' => [[
                    'operator' => 'mul',
                    'value' => '1'
                ]],
                'symbol' => 'mm²'
            ],
            [
                'code' => 'SQUARE_CENTIMETER',
                'labels' => ['en_US' => 'Square centimeter', 'fr_FR' => 'Centimètre carré'],
                'convert_from_standard' => [[
                    'operator' => 'mul',
                    'value' => '0.0001'
                ]],
                'symbol' => 'cm²'
            ]
        ];

        $expectedArea = MeasurementFamily::create(
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
                    [Operation::create('mul', '0.0001')],
                    'cm²',
                )
            ]
        );

        $measurementFamilyRepository->save(
            Argument::that(function ($area) use ($expectedArea): bool {
                Assert::eq($expectedArea, $area);
                return true;
            })
        )->shouldBeCalled();

        $this->handle($saveMeasurementFamilyCommand);
    }
}
