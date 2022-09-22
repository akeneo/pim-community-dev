<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Job;

use Akeneo\Catalogs\Infrastructure\Job\DisableCatalogsOnCategoryRemovalConstraint;
use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class DisableCatalogsOnCategoryRemovalConstraintTest extends IntegrationTestCase
{
    private ?ValidatorInterface $validator;
    private ?DisableCatalogsOnCategoryRemovalConstraint $disableCatalogConstraint;

    protected function setUp(): void
    {
        parent::setUp();

        $this->validator = self::getContainer()->get(ValidatorInterface::class);
        $this->disableCatalogConstraint = self::getContainer()->get(DisableCatalogsOnCategoryRemovalConstraint::class);

        $this->purgeData();
    }

    public function testItProvidesConstraintToValidateParameters(): void
    {
        $constraintCollection = $this->disableCatalogConstraint->getConstraintCollection();
        $parameters = ['category_code' => 'categoryA'];
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
            'missing category code' => [
                'parameters' => [],
                'expectedMessage' => 'This field is missing.',
            ],
            'category code is not a string' => [
                'parameters' => [
                    'category_code' => ['categoryA', 'categoryB'],
                ],
                'expectedMessage' => 'This value should be of type string.',
            ],
        ];
    }
}
