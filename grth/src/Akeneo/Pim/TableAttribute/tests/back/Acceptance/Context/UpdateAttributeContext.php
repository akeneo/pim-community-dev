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

namespace Akeneo\Test\Pim\TableAttribute\Acceptance\Context;

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\MeasurementColumn;
use Akeneo\Test\Acceptance\Attribute\InMemoryAttributeRepository;
use Akeneo\Test\Common\Structure\Attribute\Builder;
use Behat\Behat\Context\Context;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Webmozart\Assert\Assert;

final class UpdateAttributeContext implements Context
{
    private const ATTRIBUTE_IDENTIFIER = 'table';

    private Builder $attributeBuilder;
    private ValidatorInterface $validator;
    private InMemoryAttributeRepository $attributeRepository;
    private ConstraintViolationsContext $constraintViolationsContext;

    public function __construct(
        ValidatorInterface $validator,
        InMemoryAttributeRepository $attributeRepository,
        ConstraintViolationsContext $constraintViolationsContext
    ) {
        $this->attributeBuilder = new Builder();
        $this->validator = $validator;
        $this->attributeRepository = $attributeRepository;
        $this->constraintViolationsContext = $constraintViolationsContext;
    }

    /**
     * @Given a valid table attribute
     */
    public function aValidAttributeContext(): void
    {
        $attribute = $this->attributeBuilder
            ->withCode(self::ATTRIBUTE_IDENTIFIER)
            ->withGroupCode('marketing')
            ->withType(AttributeTypes::TABLE)
            ->build();
        $attribute->setRawTableConfiguration([
            [
                'data_type' => 'select',
                'code' => 'ingredients',
            ],
            [
                'data_type' => 'number',
                'code' => 'quantity',
            ],
            [
                'data_type' => 'boolean',
                'code' => 'isAllergenic',
            ],
            [
                'data_type' => 'text',
                'code' => 'comments'
            ],
        ]);

        $this->createAttribute($attribute);
    }

    /**
     * @Given a valid table attribute with a measurement column using :familyCode measurement family and :unitCode default unit code
     */
    public function aValidAttributeContextWithAMeasurementColumn(string $familyCode, string $unitCode): void
    {
        $attribute = $this->attributeBuilder
            ->withCode(self::ATTRIBUTE_IDENTIFIER)
            ->withGroupCode('marketing')
            ->withType(AttributeTypes::TABLE)
            ->build();
        $attribute->setRawTableConfiguration([
            [
                'data_type' => 'select',
                'code' => 'ingredients',
            ],
            [
                'data_type' => MeasurementColumn::DATATYPE,
                'code' => 'manufacturing_time',
                'measurement_family_code' => $familyCode,
                'measurement_default_unit_code' => $unitCode,
            ],
        ]);

        $this->createAttribute($attribute);
    }

    private function createAttribute(AttributeInterface $attribute): void
    {
        $violations = $this->validator->validate($attribute);

        if (0 < $violations->count()) {
            $violationMessages = [];
            foreach ($violations->getIterator() as $constraintViolation) {
                $violationMessages[] = $constraintViolation->getMessage();
            }

            throw new \LogicException(\sprintf(
                'Error during the creation of the attribute: %s',
                \implode(PHP_EOL, $violationMessages)
            ));
        }

        $this->attributeRepository->save($attribute);
    }

    /**
     * @When I update a table attribute with a valid configuration
     */
    public function iUpdateATableAttributeWithAValidConfiguration(): void
    {
        $attribute = $this->attributeBuilder
            ->withCode(self::ATTRIBUTE_IDENTIFIER)
            ->withGroupCode('marketing')
            ->withType(AttributeTypes::TABLE)
            ->build();

        $attribute->setRawTableConfiguration([
            [
                'data_type' => 'select',
                'code' => 'ingredients',
            ],
            [
                'data_type' => 'number',
                'code' => 'quantity',
            ],
            [
                'data_type' => 'text',
                'code' => 'comments'
            ],
            [
                'data_type' => 'boolean',
                'code' => 'new_column',
            ],
        ]);

        $violations = $this->validator->validate($attribute);
        if (0 < $violations->count()) {
            $this->constraintViolationsContext->add($violations);
            return;
        }

        $this->attributeRepository->save($attribute);
    }

    /**
     * @When I change a table attribute updating the first column code
     */
    public function iChangeATableAttributeUpdatingTheFirstColumnCode(): void
    {
        $attribute = $this->attributeBuilder
            ->withCode(self::ATTRIBUTE_IDENTIFIER)
            ->withGroupCode('marketing')
            ->withType(AttributeTypes::TABLE)
            ->build();

        $attribute->setRawTableConfiguration([
            [
                'data_type' => 'select',
                'code' => 'new_code',
            ],
            [
                'data_type' => 'number',
                'code' => 'quantity',
            ],
        ]);

        $violations = $this->validator->validate($attribute);
        if (0 < $violations->count()) {
            $this->constraintViolationsContext->add($violations);
            return;
        }

        $this->attributeRepository->save($attribute);
    }

    /**
     * @When I update the reference entity identifier of the :columnCode column
     */
    public function iUpdateTheReferenceEntityIdentifierForColumn(string $columnCode): void
    {
        $attribute = $this->attributeRepository->findOneByIdentifier(self::ATTRIBUTE_IDENTIFIER);
        $rawTableConfiguration = $attribute->getRawTableConfiguration();

        // we update and validate the clone in order not to modify the attribute stored in the in memory repo
        $updatedAttribute = clone $attribute;
        foreach ($rawTableConfiguration as $index => $columnDefinition) {
            if ($columnDefinition['code'] === $columnCode) {
                $rawTableConfiguration[$index]['reference_entity_identifier'] = 'designers';
            }
        }
        $updatedAttribute->setRawTableConfiguration($rawTableConfiguration);

        $violations = $this->validator->validate($updatedAttribute);
        if (0 < $violations->count()) {
            $this->constraintViolationsContext->add($violations);

            return;
        }

        $this->attributeRepository->save($updatedAttribute);
    }

    /**
     * @When I update the :localeCode label of the :columnCode column as :newLabel
     */
    public function iUpdateTheLabelsOfTheColumn(string $localeCode, string $columnCode, string $newLabel): void
    {
        $attribute = $this->attributeRepository->findOneByIdentifier(self::ATTRIBUTE_IDENTIFIER);
        $rawTableConfiguration = $attribute->getRawTableConfiguration();

        // we update and validate the clone in order not to modify the attribute stored in the in memory repo
        $updatedAttribute = clone $attribute;
        foreach ($rawTableConfiguration as $index => $columnDefinition) {
            if ($columnDefinition['code'] === $columnCode) {
                $rawTableConfiguration[$index]['labels'][$localeCode] = $newLabel;
            }
        }
        $updatedAttribute->setRawTableConfiguration($rawTableConfiguration);

        $violations = $this->validator->validate($updatedAttribute);
        if (0 < $violations->count()) {
            $this->constraintViolationsContext->add($violations);

            return;
        }

        $this->attributeRepository->save($updatedAttribute);
    }

    /**
     * @Then the :localeCode label of the :columnCode column should be :label
     */
    public function thelabelOfTheColumnShouldBe(string $localeCode, string $columnCode, string $label)
    {
        $attribute = $this->attributeRepository->findOneByIdentifier(self::ATTRIBUTE_IDENTIFIER);
        Assert::notNull($attribute);

        $tableConfiguration = $attribute->getRawTableConfiguration();
        foreach ($tableConfiguration as $columnDefinition) {
            if ($columnDefinition['code'] === $columnCode) {
                Assert::same($columnDefinition['labels'][$localeCode] ?? null, $label);

                return;
            }
        }

        throw new \LogicException(\sprintf('The %s column does not exist', $columnCode));
    }

    /**
     * @When I update the measurement family code with :familyCode value
     */
    public function updateTheMeasurementFamilyCode(string $familyCode): void
    {
        $attribute = $this->attributeBuilder
            ->withCode(self::ATTRIBUTE_IDENTIFIER)
            ->withGroupCode('marketing')
            ->withType(AttributeTypes::TABLE)
            ->build();

        $attribute->setRawTableConfiguration([
            [
                'data_type' => 'select',
                'code' => 'ingredients',
            ],
            [
                'data_type' => MeasurementColumn::DATATYPE,
                'code' => 'manufacturing_time',
                'measurement_family_code' => $familyCode,
                'measurement_default_unit_code' => 'meter',
            ],
        ]);

        $violations = $this->validator->validate($attribute);
        if (0 < $violations->count()) {
            $this->constraintViolationsContext->add($violations);
            return;
        }

        $this->attributeRepository->save($attribute);
    }

    /**
     * @When I update the measurement default unit code with :unitCode value
     */
    public function updateTheMeasurementDefaultUnitCode(string $unitCode): void
    {
        $attribute = $this->attributeBuilder
            ->withCode(self::ATTRIBUTE_IDENTIFIER)
            ->withGroupCode('marketing')
            ->withType(AttributeTypes::TABLE)
            ->build();

        $attribute->setRawTableConfiguration([
            [
                'data_type' => 'select',
                'code' => 'ingredients',
            ],
            [
                'data_type' => MeasurementColumn::DATATYPE,
                'code' => 'manufacturing_time',
                'measurement_family_code' => 'duration',
                'measurement_default_unit_code' => $unitCode,
            ],
        ]);

        $violations = $this->validator->validate($attribute);
        if (0 < $violations->count()) {
            $this->constraintViolationsContext->add($violations);
            return;
        }

        $this->attributeRepository->save($attribute);
    }

    /**
     * @Then the attribute contains the ":columnCode" column
     */
    public function theAttributeContainsTheColumn(string $columnCode): void
    {
        $attribute = $this->attributeRepository->findOneByIdentifier(self::ATTRIBUTE_IDENTIFIER);
        Assert::notNull($attribute);

        $tableConfiguration = $attribute->getRawTableConfiguration();
        foreach ($tableConfiguration as $columnDefinition) {
            if ($columnDefinition['code'] === $columnCode) {
                return;
            }
        }

        throw new \LogicException(\sprintf('Unable to find the column with "%s" code', $columnCode));
    }

    /**
     * @Then the attribute does not contain the ":columnCode" column
     */
    public function theAttributeDoesNotContainsTheColumn(string $columnCode): void
    {
        $attribute = $this->attributeRepository->findOneByIdentifier(self::ATTRIBUTE_IDENTIFIER);
        Assert::notNull($attribute);

        $tableConfiguration = $attribute->getRawTableConfiguration();
        foreach ($tableConfiguration as $columnDefinition) {
            if ($columnDefinition['code'] === $columnCode) {
                throw new \LogicException(\sprintf('The column with "%s" code should not exist', $columnCode));
            }
        }
    }
}
