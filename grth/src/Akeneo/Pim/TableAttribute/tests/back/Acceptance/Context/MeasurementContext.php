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
     * @When I create a table attribute with :code measurement family link column
     */
    public function iCreateATableAttributeWithMeasurementFamilyLinkColumn(string $code): void
    {
        $attribute = $this->attributeBuilder
            ->withCode('table')
            ->withGroupCode('marketing')
            ->withType(AttributeTypes::TABLE)
            ->build();
        $attribute->setRawTableConfiguration([
            [
                'data_type' => ReferenceEntityColumn::DATATYPE,
                'code' => 'brand',
                'reference_entity_identifier' => $referenceEntityIdentifier,
            ],
            [
                'data_type' => 'number',
                'code' => 'quantity',
            ],
        ]);
        $this->saveAttribute($attribute);
    }
}
