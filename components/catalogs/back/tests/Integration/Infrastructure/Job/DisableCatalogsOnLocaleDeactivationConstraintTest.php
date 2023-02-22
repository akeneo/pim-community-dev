<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Job;

use Akeneo\Catalogs\Infrastructure\Job\DisableCatalogsOnLocaleDeactivationConstraint;
use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class DisableCatalogsOnLocaleDeactivationConstraintTest extends IntegrationTestCase
{
    private ?ValidatorInterface $validator;
    private ?DisableCatalogsOnLocaleDeactivationConstraint $disableCatalogConstraint;

    protected function setUp(): void
    {
        parent::setUp();

        $this->validator = self::getContainer()->get(ValidatorInterface::class);
        $this->disableCatalogConstraint = self::getContainer()->get(DisableCatalogsOnLocaleDeactivationConstraint::class);

        $this->purgeData();
    }

    public function testItProvidesConstraintToValidateParameters(): void
    {
        $constraintCollection = $this->disableCatalogConstraint->getConstraintCollection();
        $parameters = ['locale_codes' => ['fr_FR', 'en_US']];
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
            'missing locale codes' => [
                'parameters' => [],
                'expectedMessage' => 'This field is missing.',
            ],
            'locale codes is not an array' => [
                'parameters' => [
                    'locale_codes' => 'fr_FR',
                ],
                'expectedMessage' => 'This value should be of type array.',
            ],
            'locale code is not an string' => [
                'parameters' => [
                    'locale_codes' => ['fr_FR', 2, 'en_US'],
                ],
                'expectedMessage' => 'This value should be of type string.',
            ],
        ];
    }
}
