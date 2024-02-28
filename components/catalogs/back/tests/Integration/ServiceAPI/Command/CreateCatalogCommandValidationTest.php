<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\ServiceAPI\Command;

use Akeneo\Catalogs\Application\Persistence\Catalog\IsCatalogsNumberLimitReachedQueryInterface;
use Akeneo\Catalogs\Infrastructure\Validation\MaxNumberOfCatalogsPerUserValidator;
use Akeneo\Catalogs\ServiceAPI\Command\CreateCatalogCommand;
use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CreateCatalogCommandValidationTest extends IntegrationTestCase
{
    private ?ValidatorInterface $validator;
    private ?IsCatalogsNumberLimitReachedQueryInterface $isCatalogsNumberLimitReachedQuery;

    protected function setUp(): void
    {
        parent::setUp();

        $this->isCatalogsNumberLimitReachedQuery = $this->createMock(IsCatalogsNumberLimitReachedQueryInterface::class);

        self::getContainer()->set(
            MaxNumberOfCatalogsPerUserValidator::class,
            new MaxNumberOfCatalogsPerUserValidator($this->isCatalogsNumberLimitReachedQuery),
        );

        $this->validator = self::getContainer()->get(ValidatorInterface::class);
    }

    /**
     * @dataProvider validations
     */
    public function testItValidatesTheCommand(CreateCatalogCommand $command, string $error): void
    {
        $violations = $this->validator->validate($command);

        $this->assertViolationsListContains($violations, $error);
    }

    public function validations(): array
    {
        return [
            'id is not empty' => [
                'command' => new CreateCatalogCommand(
                    id: '',
                    name: 'foo',
                    ownerUsername: 'shopifi',
                ),
                'error' => 'This value should not be blank.',
            ],
            'id is an uuid' => [
                'command' => new CreateCatalogCommand(
                    id: 'not an uuid',
                    name: 'foo',
                    ownerUsername: 'shopifi',
                ),
                'error' => 'This value is not a valid UUID.',
            ],
            'name is not empty' => [
                'command' => new CreateCatalogCommand(
                    id: '43c74e94-0074-4316-ac66-93cd0ca71a6b',
                    name: '',
                    ownerUsername: 'shopifi',
                ),
                'error' => 'This value is too short. It should have 1 character or more.',
            ],
            'name is not too long' => [
                'command' => new CreateCatalogCommand(
                    id: '43c74e94-0074-4316-ac66-93cd0ca71a6b',
                    name: 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus placerat ante id dui ornare feugiat. Nulla egestas neque eu lectus interdum congue nec at diam. Phasellus ac magna lorem. Praesent non lectus sit amet lectus sollicitudin consectetur sed non.',
                    ownerUsername: 'shopifi',
                ),
                'error' => 'This value is too long. It should have 255 characters or less.',
            ],
        ];
    }

    public function testItValidatesThatTheLimitOfCatalogsByUserIsNotReached(): void
    {
        $this->isCatalogsNumberLimitReachedQuery->method('execute')->willReturn(true);

        $violations = self::getContainer()->get(ValidatorInterface::class)->validate(
            new CreateCatalogCommand(
                id: '43c74e94-0074-4316-ac66-93cd0ca71a6b',
                name: 'Store US',
                ownerUsername: 'shopifi',
            )
        );

        $this->assertViolationsListContains($violations, 'You can create up to 15 catalogs');
    }
}
