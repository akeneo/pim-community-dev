<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Test\Pim\TableAttribute\Acceptance\Context;

use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Query\MeasurementFamilyExists;
use Akeneo\Tool\Bundle\MeasureBundle\Application\CreateMeasurementFamily\CreateMeasurementFamilyCommand;
use Akeneo\Tool\Bundle\MeasureBundle\Application\CreateMeasurementFamily\CreateMeasurementFamilyHandler;
use Akeneo\Tool\Bundle\MeasureBundle\Application\DeleteMeasurementFamily\DeleteMeasurementFamilyCommand;
use Akeneo\Tool\Bundle\MeasureBundle\Application\DeleteMeasurementFamily\DeleteMeasurementFamilyHandler;
use Akeneo\Tool\Bundle\MeasureBundle\Application\SaveMeasurementFamily\SaveMeasurementFamilyCommand;
use Akeneo\Tool\Bundle\MeasureBundle\Application\SaveMeasurementFamily\SaveMeasurementFamilyHandler;
use Akeneo\Tool\Bundle\MeasureBundle\Model\MeasurementFamilyCode;
use Akeneo\Tool\Bundle\MeasureBundle\Persistence\MeasurementFamilyRepositoryInterface;
use Behat\Behat\Context\Context;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Webmozart\Assert\Assert;

final class MeasurementContext implements Context
{
    public function __construct(
        private ValidatorInterface $validator,
        private CreateMeasurementFamilyHandler $createMeasurementFamilyHandler,
        private DeleteMeasurementFamilyHandler $deleteMeasurementFamilyHandler,
        private ConstraintViolationsContext $constraintViolationsContext,
        private MeasurementFamilyExists $measurementFamilyExists,
        private MeasurementFamilyRepositoryInterface $measurementFamilyRepository,
        private SaveMeasurementFamilyHandler $measurementFamilyHandler,
    ) {
    }

    /**
     * @Given the :code measurement family with the :units units
     */
    public function theMeasurementFamily(string $code, string $units): void
    {
        $unitCodes = \explode(',', $units);
        $createCommand = new CreateMeasurementFamilyCommand();
        $createCommand->code = $code;
        $createCommand->labels = [];
        $createCommand->standardUnitCode = $unitCodes[0];
        $createCommand->units = \array_map(
            static fn (string $unitCode): array => [
                'code' => $unitCode,
                'labels' => [],
                'convert_from_standard' => [['operator' => 'mul', 'value' => '1']],
                'symbol' => $unitCode,
            ],
            $unitCodes
        );

        $violations = $this->validator->validate($createCommand);
        Assert::count($violations, 0, (string) $violations);
        $this->createMeasurementFamilyHandler->handle($createCommand);
    }

    /**
     * @When I delete the :code measurement family
     */
    public function iDeleteTheMeasurementFamily(string $code): void
    {
        $deleteCommand = new DeleteMeasurementFamilyCommand();
        $deleteCommand->code = $code;

        $violations = $this->validator->validate($deleteCommand);
        if ($violations->count() > 0) {
            $this->constraintViolationsContext->add($violations);

            return;
        }

        $this->deleteMeasurementFamilyHandler->handle($deleteCommand);
    }

    /**
     * @Then The :code measurement family was deleted
     */
    public function theMeasurementFamilyWasDeleted(string $code): void
    {
        Assert::false(
            $this->measurementFamilyExists->forCode($code),
            \sprintf('the %s measurement family was not deleted.', $code)
        );
    }

    /**
     * @Then The :code measurement family was not deleted
     */
    public function theMeasurementFamilyWasNotDeleted(string $code): void
    {
        Assert::true(
            $this->measurementFamilyExists->forCode($code),
            \sprintf('the %s measurement family was deleted.', $code)
        );
    }

    /**
     * @When I remove the :removedUnitCode unit from the :measurementFamilyCode family
     */
    public function iRemoveTheUnitFromTheMeasurementFamily(string $removedUnitCode, string $measurementFamilyCode): void
    {
        $measurementFamily = $this->measurementFamilyRepository
            ->getByCode(MeasurementFamilyCode::fromString($measurementFamilyCode));

        $normalized = $measurementFamily->normalize();

        $command = new SaveMeasurementFamilyCommand();
        $command->code = $measurementFamilyCode;
        $command->standardUnitCode = $normalized['standard_unit_code'];
        $command->labels = $normalized['labels'];
        $command->units = \array_filter(
            $normalized['units'],
            fn (array $normalizedUnit): bool => $normalizedUnit['code'] !== $removedUnitCode
        );

        $violations = $this->validator->validate($command);
        if ($violations->count() > 0) {
            $this->constraintViolationsContext->add($violations);

            return;
        }

        $this->measurementFamilyHandler->handle($command);
    }

    /**
     * @Then The :measurementFamilyCode measurement family contains the :unitCode unit
     */
    public function thefamilyContainsTheUnit(string $measurementFamilyCode, string $unitCode): void
    {
        $measurementFamily = $this->measurementFamilyRepository
            ->getByCode(MeasurementFamilyCode::fromString($measurementFamilyCode));

        Assert::keyExists(
            $measurementFamily->normalizeWithIndexedUnits()['units'],
            $unitCode,
            \sprintf('The %s unit was deleted', $unitCode)
        );
    }

    /**
     * @When I add a step for the :unitCode conversion operation in the :measurementFamilyCode family
     */
    public function iAddAStepForTheConversionOperationIntheFamily(string $unitCode, string $measurementFamilyCode): void
    {
        $measurementFamily = $this->measurementFamilyRepository
            ->getByCode(MeasurementFamilyCode::fromString($measurementFamilyCode));

        $normalized = $measurementFamily->normalizeWithIndexedUnits();
        $normalized['units'][$unitCode]['convert_from_standard'][] = ['operator' => 'add', 'value' => '5.8'];

        $command = new SaveMeasurementFamilyCommand();
        $command->code = $measurementFamilyCode;
        $command->standardUnitCode = $normalized['standard_unit_code'];
        $command->labels = $normalized['labels'];
        $command->units = $normalized['units'];

        $violations = $this->validator->validate($command);
        if ($violations->count() > 0) {
            $this->constraintViolationsContext->add($violations);

            return;
        }

        $this->measurementFamilyHandler->handle($command);
    }

    /**
     * @When I add the :unitCode unit and update the labels of the :measurementFamilyCode family to :jsonLabels
     */
    public function addUnitAndUpdateLabelsOfFamily(
        string $unitCode,
        string $measurementFamilyCode,
        string $jsonLabels
    ) {
        $measurementFamily = $this->measurementFamilyRepository
            ->getByCode(MeasurementFamilyCode::fromString($measurementFamilyCode));

        $normalized = $measurementFamily->normalizeWithIndexedUnits();

        $normalized['units'][$unitCode] = [
            'code' => $unitCode,
            'labels' => [],
            'convert_from_standard' => [['operator' => 'mul', 'value' => '1']],
            'symbol' => $unitCode,
        ];

        $normalized['labels'] = json_decode($jsonLabels, true);

        $command = new SaveMeasurementFamilyCommand();
        $command->code = $measurementFamilyCode;
        $command->standardUnitCode = $normalized['standard_unit_code'];
        $command->labels = $normalized['labels'];
        $command->units = $normalized['units'];

        $violations = $this->validator->validate($command);
        if ($violations->count() > 0) {
            $this->constraintViolationsContext->add($violations);

            return;
        }

        $this->measurementFamilyHandler->handle($command);
    }

    /**
     * @Then The labels of the :measurementFamilyCode family were updated to :jsonLabels
     */
    public function labelsOfFamilyWereUpdated(
        string $measurementFamilyCode,
        string $jsonLabels
    ) {
        $measurementFamily = $this->measurementFamilyRepository
            ->getByCode(MeasurementFamilyCode::fromString($measurementFamilyCode));

        $normalizedLabels = $measurementFamily->normalize()['labels'];

        Assert::same($normalizedLabels, json_decode($jsonLabels, true));
    }
}
