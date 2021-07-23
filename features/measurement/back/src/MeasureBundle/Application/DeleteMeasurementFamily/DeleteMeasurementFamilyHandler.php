<?php

declare(strict_types=1);

namespace AkeneoMeasureBundle\Application\DeleteMeasurementFamily;

use AkeneoMeasureBundle\Event\MeasurementFamilyDeleted;
use AkeneoMeasureBundle\Exception\MeasurementFamilyNotFoundException;
use AkeneoMeasureBundle\Model\MeasurementFamilyCode;
use AkeneoMeasureBundle\Persistence\MeasurementFamilyRepositoryInterface;
use Psr\EventDispatcher\EventDispatcherInterface;

/**
 * @copyright 2020 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class DeleteMeasurementFamilyHandler
{
    private MeasurementFamilyRepositoryInterface $measurementFamilyRepository;
    private ?EventDispatcherInterface $eventDispatcher;

    public function __construct(
        MeasurementFamilyRepositoryInterface $measurementFamilyRepository,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->measurementFamilyRepository = $measurementFamilyRepository;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @throws MeasurementFamilyNotFoundException
     */
    public function handle(DeleteMeasurementFamilyCommand $deleteMeasurementFamilyCommand): void
    {
        $measurementFamilyCode = MeasurementFamilyCode::fromString($deleteMeasurementFamilyCommand->code);
        $this->measurementFamilyRepository->deleteByCode($measurementFamilyCode);
        $this->eventDispatcher->dispatch(new MeasurementFamilyDeleted($measurementFamilyCode));
    }
}
