<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\FreeTrial\Infrastructure\Install\Installer;

use Akeneo\FreeTrial\Infrastructure\Install\Reader\FixtureReader;
use Akeneo\Tool\Bundle\MeasureBundle\Application\CreateMeasurementFamily\CreateMeasurementFamilyCommand;
use Akeneo\Tool\Bundle\MeasureBundle\Application\CreateMeasurementFamily\CreateMeasurementFamilyHandler;
use Akeneo\Tool\Bundle\MeasureBundle\Application\SaveMeasurementFamily\SaveMeasurementFamilyCommand;
use Akeneo\Tool\Bundle\MeasureBundle\Application\SaveMeasurementFamily\SaveMeasurementFamilyHandler;
use Akeneo\Tool\Bundle\MeasureBundle\Exception\MeasurementFamilyNotFoundException;
use Akeneo\Tool\Bundle\MeasureBundle\Model\MeasurementFamily;
use Akeneo\Tool\Bundle\MeasureBundle\Model\MeasurementFamilyCode;
use Akeneo\Tool\Bundle\MeasureBundle\Persistence\MeasurementFamilyRepositoryInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class MeasurementFamilyInstaller implements FixtureInstaller
{
    private FixtureReader $fixtureReader;

    private ValidatorInterface $validator;

    private SaveMeasurementFamilyHandler $saveMeasurementFamilyHandler;

    private CreateMeasurementFamilyHandler $createMeasurementFamilyHandler;

    private MeasurementFamilyRepositoryInterface $measurementFamilyRepository;

    public function __construct(
        FixtureReader $fixtureReader,
        ValidatorInterface $validator,
        SaveMeasurementFamilyHandler $saveMeasurementFamilyHandler,
        CreateMeasurementFamilyHandler $createMeasurementFamilyHandler,
        MeasurementFamilyRepositoryInterface $measurementFamilyRepository
    ) {
        $this->fixtureReader = $fixtureReader;
        $this->validator = $validator;
        $this->saveMeasurementFamilyHandler = $saveMeasurementFamilyHandler;
        $this->createMeasurementFamilyHandler = $createMeasurementFamilyHandler;
        $this->measurementFamilyRepository = $measurementFamilyRepository;
    }

    public function install(): void
    {
        foreach ($this->fixtureReader->read() as $measurementFamilyData) {
            $measurementFamilyCode = MeasurementFamilyCode::fromString($measurementFamilyData['code']);
            $measurementFamily = $this->findMeasurementFamily($measurementFamilyCode);

            if (null === $measurementFamily) {
                $this->createMeasurementFamily($measurementFamilyData);
            } else {
                $this->updateMeasurementFamily($measurementFamilyData, $measurementFamily);
            }
        }
    }

    private function findMeasurementFamily(MeasurementFamilyCode $measurementFamilyCode): ?MeasurementFamily
    {
        try {
            return $this->measurementFamilyRepository->getByCode($measurementFamilyCode);
        } catch (MeasurementFamilyNotFoundException $exception) {
            return null;
        }
    }

    private function createMeasurementFamily(array $measurementFamilyData): void
    {
        $createMeasurementFamilyCommand = $this->createMeasurementFamilyCommand($measurementFamilyData);

        $violations = $this->validator->validate($createMeasurementFamilyCommand);
        if ($violations->count() > 0) {
            throw new \Exception(sprintf(
                'validation failed on measurement family "%s" with message: "%s"',
                $measurementFamilyData['code'],
                iterator_to_array($violations)[0]->getMessage()
            ));
        }

        $this->createMeasurementFamilyHandler->handle($createMeasurementFamilyCommand);
    }

    private function updateMeasurementFamily(array $measurementFamilyData, MeasurementFamily $measurementFamily): void
    {
        $normalizedMeasurementFamily = array_replace_recursive(
            $measurementFamily->normalizeWithIndexedUnits(),
            $measurementFamilyData
        );

        $saveMeasurementFamilyCommand = $this->saveMeasurementFamilyCommand($normalizedMeasurementFamily);

        $violations = $this->validator->validate($saveMeasurementFamilyCommand);
        if ($violations->count() > 0) {
            throw new \Exception(sprintf(
                'validation failed on measurement family "%s" with message: "%s"',
                $measurementFamilyData['code'],
                iterator_to_array($violations)[0]->getMessage()
            ));
        }

        $this->saveMeasurementFamilyHandler->handle($saveMeasurementFamilyCommand);
    }

    private function saveMeasurementFamilyCommand(array $normalizedMeasurementFamily): SaveMeasurementFamilyCommand
    {
        $command = new SaveMeasurementFamilyCommand;
        $command->code = $normalizedMeasurementFamily['code'];
        $command->standardUnitCode = $normalizedMeasurementFamily['standard_unit_code'];
        $command->labels = $normalizedMeasurementFamily['labels'] ?? [];
        $command->units = $this->getNormalizedUnitsFromNormalizedMeasurementFamily($normalizedMeasurementFamily);

        return $command;
    }

    private function createMeasurementFamilyCommand(array $measurementFamilyData): CreateMeasurementFamilyCommand
    {
        $command = new CreateMeasurementFamilyCommand();
        $command->code = $measurementFamilyData['code'];
        $command->standardUnitCode = $measurementFamilyData['standard_unit_code'];
        $command->labels = $measurementFamilyData['labels'] ?? [];
        $command->units = $this->getNormalizedUnitsFromNormalizedMeasurementFamily($measurementFamilyData);

        return $command;
    }

    private function getNormalizedUnitsFromNormalizedMeasurementFamily(array $normalizedMeasurementFamily): array
    {
        return array_map(function (array $unit) {
            return [
              'code' => $unit['code'],
              'convert_from_standard' => $unit['convert_from_standard'],
              'labels' => $unit['labels'] ?? [],
              'symbol' => $unit['symbol'] ?? '',
            ];
        }, array_values($normalizedMeasurementFamily['units'] ?? []));
    }
}
