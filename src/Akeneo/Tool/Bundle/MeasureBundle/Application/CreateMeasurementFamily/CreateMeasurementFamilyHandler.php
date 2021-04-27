<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\MeasureBundle\Application\CreateMeasurementFamily;

use Akeneo\Tool\Bundle\MeasureBundle\Event\MeasurementFamilyCreated;
use Akeneo\Tool\Bundle\MeasureBundle\Model\LabelCollection;
use Akeneo\Tool\Bundle\MeasureBundle\Model\MeasurementFamily;
use Akeneo\Tool\Bundle\MeasureBundle\Model\MeasurementFamilyCode;
use Akeneo\Tool\Bundle\MeasureBundle\Model\Operation;
use Akeneo\Tool\Bundle\MeasureBundle\Model\Unit;
use Akeneo\Tool\Bundle\MeasureBundle\Model\UnitCode;
use Akeneo\Tool\Bundle\MeasureBundle\Persistence\MeasurementFamilyRepositoryInterface;
use Psr\EventDispatcher\EventDispatcherInterface;

/**
 * @copyright 2020 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class CreateMeasurementFamilyHandler
{
    /** @var MeasurementFamilyRepositoryInterface */
    private $measurementFamilyRepository;

    private ?EventDispatcherInterface $eventDispatcher;

    /** TODO: @pullup Remove = null */
    public function __construct(
        MeasurementFamilyRepositoryInterface $measurementFamilyRepository,
        EventDispatcherInterface $eventDispatcher = null
    ) {
        $this->measurementFamilyRepository = $measurementFamilyRepository;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function handle(CreateMeasurementFamilyCommand $saveMeasurementFamilyCommand): void
    {
        $units = $this->units($saveMeasurementFamilyCommand);
        $measurementFamilyCode = MeasurementFamilyCode::fromString($saveMeasurementFamilyCommand->code);
        $measurementFamily = MeasurementFamily::create(
            $measurementFamilyCode,
            LabelCollection::fromArray($saveMeasurementFamilyCommand->labels),
            UnitCode::fromString($saveMeasurementFamilyCommand->standardUnitCode),
            $units
        );

        $this->measurementFamilyRepository->save($measurementFamily);
        if (null !== $this->eventDispatcher) {
            $this->eventDispatcher->dispatch(new MeasurementFamilyCreated($measurementFamilyCode));
        }
    }

    private function units(CreateMeasurementFamilyCommand $saveMeasurementFamilyCommand): array
    {
        return array_map(
            function (array $unit) {
                $operations = array_map(
                    function (array $operation) {
                        return Operation::create(
                            $operation['operator'],
                            $operation['value']
                        );
                    },
                    $unit['convert_from_standard']
                );

                return Unit::create(
                    UnitCode::fromString($unit['code']),
                    LabelCollection::fromArray($unit['labels']),
                    $operations,
                    $unit['symbol']
                );
            },
            $saveMeasurementFamilyCommand->units
        );
    }
}
