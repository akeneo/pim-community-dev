<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Job;

use Akeneo\Catalogs\Infrastructure\Job\NotifyOnAttributeRemovalConstraint;
use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class NotifyOnAttributeRemovalConstraintTest extends IntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->purgeData();
    }

    public function testItProvidesConstraintToValidateParameters(): void
    {
        $constraintCollection = self::getContainer()->get(NotifyOnAttributeRemovalConstraint::class)->getConstraintCollection();
        $parameters = ['attribute_codes' => ['name', 'description']];
        $violations = self::getContainer()->get(ValidatorInterface::class)->validate($parameters, $constraintCollection);

        $this->assertEmpty($violations);
    }

    /**
     * @dataProvider invalidParametersDataProvider
     */
    public function testItProvidesConstraintToInvalidateParameters(
        array $parameters,
        string $expectedMessage,
    ): void {
        $constraintCollection = self::getContainer()->get(NotifyOnAttributeRemovalConstraint::class)->getConstraintCollection();
        $violations = self::getContainer()->get(ValidatorInterface::class)->validate($parameters, $constraintCollection);

        $this->assertViolationsListContains($violations, $expectedMessage);
    }

    public function invalidParametersDataProvider(): array
    {
        return [
            'missing attribute codes' => [
                'parameters' => [],
                'expectedMessage' => 'This field is missing.',
            ],
            'attribute codes is not an array' => [
                'parameters' => [
                    'attribute_codes' => 'name',
                ],
                'expectedMessage' => 'This value should be of type array.',
            ],
            'attribute code is not an string' => [
                'parameters' => [
                    'attribute_codes' => ['name', 2, 'description'],
                ],
                'expectedMessage' => 'This value should be of type string.',
            ],
        ];
    }
}
