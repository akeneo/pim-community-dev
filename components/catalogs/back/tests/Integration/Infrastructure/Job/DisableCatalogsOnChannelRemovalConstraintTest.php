<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Job;

use Akeneo\Catalogs\Infrastructure\Job\DisableCatalogsOnChannelRemovalConstraint;
use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class DisableCatalogsOnChannelRemovalConstraintTest extends IntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->purgeData();
    }

    public function testItProvidesConstraintToValidateParameters(): void
    {
        $constraintCollection = self::getContainer()->get(
            DisableCatalogsOnChannelRemovalConstraint::class,
        )->getConstraintCollection();
        $parameters = ['channel_codes' => ['print', 'mobile']];
        $violations = self::getContainer()->get(ValidatorInterface::class)->validate(
            $parameters,
            $constraintCollection,
        );

        $this->assertEmpty($violations);
    }

    /**
     * @dataProvider invalidParametersDataProvider
     */
    public function testItProvidesConstraintToInvalidateParameters(
        array $parameters,
        string $expectedMessage,
    ): void {
        $constraintCollection = self::getContainer()->get(
            DisableCatalogsOnChannelRemovalConstraint::class,
        )->getConstraintCollection();
        $violations = self::getContainer()->get(ValidatorInterface::class)->validate(
            $parameters,
            $constraintCollection,
        );

        $this->assertViolationsListContains($violations, $expectedMessage);
    }

    public function invalidParametersDataProvider(): array
    {
        return [
            'missing channel codes' => [
                'parameters' => [],
                'expectedMessage' => 'This field is missing.',
            ],
            'channel codes is not an array' => [
                'parameters' => [
                    'channel_codes' => 'print',
                ],
                'expectedMessage' => 'This value should be of type array.',
            ],
            'channel code is not an string' => [
                'parameters' => [
                    'channel_codes' => ['print', 2, 'mobile'],
                ],
                'expectedMessage' => 'This value should be of type string.',
            ],
        ];
    }
}
