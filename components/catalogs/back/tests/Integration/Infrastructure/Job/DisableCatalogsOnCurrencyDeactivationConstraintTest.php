<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Job;

use Akeneo\Catalogs\Infrastructure\Job\DisableCatalogsOnCurrencyDeactivationConstraint;
use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class DisableCatalogsOnCurrencyDeactivationConstraintTest extends IntegrationTestCase
{
    private ?ValidatorInterface $validator;
    private ?DisableCatalogsOnCurrencyDeactivationConstraint $disableCatalogConstraint;

    protected function setUp(): void
    {
        parent::setUp();

        $this->validator = self::getContainer()->get(ValidatorInterface::class);
        $this->disableCatalogConstraint = self::getContainer()->get(DisableCatalogsOnCurrencyDeactivationConstraint::class);

        $this->purgeData();
    }

    public function testItProvidesConstraintToValidateParameters(): void
    {
        $constraintCollection = $this->disableCatalogConstraint->getConstraintCollection();
        $parameters = ['currency_codes' => ['EUR', 'USD']];
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
            'missing currency codes' => [
                'parameters' => [],
                'expectedMessage' => 'This field is missing.',
            ],
            'currency codes is not an array' => [
                'parameters' => [
                    'currency_codes' => 'EUR',
                ],
                'expectedMessage' => 'This value should be of type array.',
            ],
            'currency code is not an string' => [
                'parameters' => [
                    'currency_codes' => ['EUR', 2, 'USD'],
                ],
                'expectedMessage' => 'This value should be of type string.',
            ],
        ];
    }
}
