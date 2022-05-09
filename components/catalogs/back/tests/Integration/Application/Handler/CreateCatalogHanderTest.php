<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Application\Handler;

use Akeneo\Catalogs\Domain\Command\CreateCatalogCommand;
use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CreateCatalogHanderTest extends IntegrationTestCase
{
    private ValidatorInterface $validator;

    public function setUp(): void
    {
        parent::setUp();

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
                ),
                'error' => 'This value should not be blank.',
            ],
            'id is an uuid' => [
                'command' => new CreateCatalogCommand(
                    id: 'not an uuid',
                    name: 'foo',
                ),
                'error' => 'This is not a valid UUID.',
            ],
            'name is not empty' => [
                'command' => new CreateCatalogCommand(
                    id: '43c74e94-0074-4316-ac66-93cd0ca71a6b',
                    name: '',
                ),
                'error' => 'This value is too short. It should have 1 character or more.',
            ],
            'name is not too long' => [
                'command' => new CreateCatalogCommand(
                    id: '43c74e94-0074-4316-ac66-93cd0ca71a6b',
                    name: 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus placerat ante id dui ornare feugiat. Nulla egestas neque eu lectus interdum congue nec at diam. Phasellus ac magna lorem. Praesent non lectus sit amet lectus sollicitudin consectetur sed non.',
                ),
                'error' => 'This value is too long. It should have 255 characters or less.',
            ],
        ];
    }
}
