<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Job;

use Akeneo\Catalogs\Infrastructure\Job\DisableCatalogsOnAttributeOptionRemovalConstraint;
use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class DisableCatalogsOnAttributeOptionRemovalConstraintTest extends IntegrationTestCase
{
    private ?ValidatorInterface $validator;
    private ?DisableCatalogsOnAttributeOptionRemovalConstraint $disableCatalogConstraint;

    protected function setUp(): void
    {
        parent::setUp();

        $this->validator = self::getContainer()->get(ValidatorInterface::class);
        $this->disableCatalogConstraint = self::getContainer()->get(DisableCatalogsOnAttributeOptionRemovalConstraint::class);

        $this->purgeData();
    }

    public function testItProvidesConstraintToValidateParameters(): void
    {
        $constraintCollection = $this->disableCatalogConstraint->getConstraintCollection();
        $parameters = ['attribute_code' => 'color', 'attribute_option_code' => 'red'];
        $violations = $this->validator->validate($parameters, $constraintCollection);

        $this->assertEmpty($violations);
    }

    /**
     * @dataProvider invalidParametersDataProvider
     */
    public function testItProvidesConstraintToInvalidateParameters(
        array $parameters,
        string $expectedMessage,
    ): void {
        $constraintCollection = $this->disableCatalogConstraint->getConstraintCollection();
        $violations = $this->validator->validate($parameters, $constraintCollection);

        $this->assertViolationsListContains($violations, $expectedMessage);
    }

    public function invalidParametersDataProvider(): array
    {
        return [
            'missing attribute code' => [
                'parameters' => [
                    'attribute_option_code' => 'red',
                ],
                'expectedMessage' => 'This field is missing.',
            ],
            'missing attribute option code' => [
                'parameters' => [
                    'attribute_code' => 'color',
                ],
                'expectedMessage' => 'This field is missing.',
            ],
            'attribute code is not a string' => [
                'parameters' => [
                    'attribute_code' => ['color', 'size'],
                    'attribute_option_code' => 'red',
                ],
                'expectedMessage' => 'This value should be of type string.',
            ],
            'attribute option code is not a string' => [
                'parameters' => [
                    'attribute_code' => 'color',
                    'attribute_option_code' => ['red', 'blue'],
                ],
                'expectedMessage' => 'This value should be of type string.',
            ],
        ];
    }
}
