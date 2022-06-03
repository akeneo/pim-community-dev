<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\ServiceAPI\Command;

use Akeneo\Catalogs\Infrastructure\Service\GetCatalogsNumberLimit;
use Akeneo\Catalogs\ServiceAPI\Command\CreateCatalogCommand;
use Akeneo\Catalogs\ServiceAPI\Messenger\CommandBus;
use Akeneo\Catalogs\Test\CatalogLoader;
use Akeneo\Catalogs\Test\ConnectionLoader;
use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;
use Akeneo\UserManagement\Component\Repository\UserRepositoryInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CreateCatalogCommandValidationTest extends IntegrationTestCase
{
    private ?ValidatorInterface $validator;
    private ?GetCatalogsNumberLimit $getCatalogsNumberLimit;
    private ?CommandBus $commandBus;
    private ?UserRepositoryInterface $userRepository;
    private ?TokenStorageInterface $tokenStorage;

    public function setUp(): void
    {
        parent::setUp();

        $this->getCatalogsNumberLimit = self::getContainer()->get(GetCatalogsNumberLimit::class);
        $this->validator = self::getContainer()->get(ValidatorInterface::class);
        $this->commandBus = self::getContainer()->get(CommandBus::class);
        $this->userRepository = self::getContainer()->get('pim_user.repository.user');
        $this->tokenStorage = self::getContainer()->get(TokenStorageInterface::class);

        $this->purgeDataAndLoadMinimalCatalog();
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
                    ownerId: 42,
                ),
                'error' => 'This value should not be blank.',
            ],
            'id is an uuid' => [
                'command' => new CreateCatalogCommand(
                    id: 'not an uuid',
                    name: 'foo',
                    ownerId: 42,
                ),
                'error' => 'This is not a valid UUID.',
            ],
            'name is not empty' => [
                'command' => new CreateCatalogCommand(
                    id: '43c74e94-0074-4316-ac66-93cd0ca71a6b',
                    name: '',
                    ownerId: 42,
                ),
                'error' => 'This value is too short. It should have 1 character or more.',
            ],
            'name is not too long' => [
                'command' => new CreateCatalogCommand(
                    id: '43c74e94-0074-4316-ac66-93cd0ca71a6b',
                    name: 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus placerat ante id dui ornare feugiat. Nulla egestas neque eu lectus interdum congue nec at diam. Phasellus ac magna lorem. Praesent non lectus sit amet lectus sollicitudin consectetur sed non.',
                    ownerId: 42,
                ),
                'error' => 'This value is too long. It should have 255 characters or less.',
            ],
        ];
    }

    public function testItDoesNotValidateTheCommandWhenCountIsAboveTheLimit()
    {
        $limit = 2;
        $this->getCatalogsNumberLimit->setLimit($limit);

        $owner = $this->createUser('owner');
        $ownerId = $owner->getId();

        $this->commandBus->execute(new CreateCatalogCommand(
            'db1079b6-f397-4a6a-bae4-8658e64ad47c',
            'Store US',
            $ownerId
        ));
        $this->commandBus->execute(new CreateCatalogCommand(
            'ed30425c-d9cf-468b-8bc7-fa346f41dd07',
            'Store FR',
            $ownerId
        ));

        $command = new CreateCatalogCommand(
            '27c53e59-ee6a-4215-a8f1-2fccbb67ba0d',
            'Store UK',
            $ownerId
        );

        $violations = $this->validator->validate($command);

        $this->assertViolationsListContains($violations, \sprintf('You can create up to %s catalogs per app', $limit));
    }
}
