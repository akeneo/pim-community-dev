<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Command;

use Akeneo\Catalogs\ServiceAPI\Command\CreateCatalogCommand;
use Akeneo\Catalogs\ServiceAPI\Command\UpdateProductMappingSchemaCommand;
use Akeneo\Catalogs\ServiceAPI\Messenger\CommandBus;
use Akeneo\Connectivity\Connection\ServiceApi\Service\ConnectedAppFactory;
use Akeneo\Connectivity\Connection\ServiceApi\Service\ConnectedAppRemover;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Akeneo\UserManagement\Component\Repository\UserRepositoryInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CatalogFixtureCommand extends Command
{
    protected static $defaultName = 'akeneo:catalogs:fixtures';
    protected static $defaultDescription = 'Do not run this command in production env. Installs fixtures for dev only.';

    public function __construct(
        private ConnectedAppFactory $connectedAppFactory,
        private CommandBus $commandBus,
        private UserRepositoryInterface $userRepository,
        private ConnectedAppRemover $connectedAppRemover,
        private Connection $connection,
        private string $env,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setHelp('This command allows you to create a catalog and the associated connected App.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if ('dev' !== $this->env) {
            $output->writeln('This command must be run in dev environment.');

            return self::INVALID;
        }

        $this->connection->beginTransaction();

        try {
            try {
                $this->connectedAppRemover->remove('555d7447-2dab-474e-9026-f5d33c401b74');
            } catch (\Throwable $exception) {
            }

            $connectedApp = $this->connectedAppFactory->createFakeConnectedAppWithValidToken(
                '555d7447-2dab-474e-9026-f5d33c401b74',
                'shopifi',
                [
                    'read_catalogs',
                    'write_catalogs',
                    'delete_catalogs',
                    'read_products',
                ],
            );

            /** @var UserInterface|null $user */
            $user = $this->userRepository->findOneBy(['username' => $connectedApp->getUsername()]);
            \assert(null !== $user);

            $this->commandBus->execute(new CreateCatalogCommand(
                Uuid::uuid4()->toString(),
                'Store US',
                $user->getUserIdentifier(),
            ));

            $this->commandBus->execute(new CreateCatalogCommand(
                Uuid::uuid4()->toString(),
                'Store FR',
                $user->getUserIdentifier(),
            ));

            $catalogWithMappingId = Uuid::uuid4()->toString();

            $this->commandBus->execute(new CreateCatalogCommand(
                $catalogWithMappingId,
                'Store with Mapping',
                $user->getUserIdentifier(),
            ));

            $productMapping = [
                'uuid' => [
                    'source' => 'uuid',
                    'scope' => null,
                    'locale' => null,
                ],
                'name' => [
                    'source' => 'name',
                    'scope' => null,
                    'locale' => null,
                ],
                'description' => [
                    'source' => null,
                    'scope' => null,
                    'locale' => null,
                ],
                'erp_name' => [
                    'source' => 'erp_name',
                    'scope' => null,
                    'locale' => 'en_US',
                ],
                'meta_title' => [
                    'source' => 'meta_title',
                    'scope' => null,
                    'locale' => 'en_US',
                ],
            ];

            $this->setCatalogProductMapping($catalogWithMappingId, $productMapping);

            /** @var object $productMappingSchema */
            $productMappingSchema = \json_decode($this->getProductMappingSchemaRaw(), false, 512, JSON_THROW_ON_ERROR);

            $this->commandBus->execute(new UpdateProductMappingSchemaCommand(
                $catalogWithMappingId,
                $productMappingSchema,
            ));

            $this->connection->commit();

            return self::SUCCESS;
        } catch (\Exception $exception) {
            $this->connection->rollBack();

            $output->writeln($exception->getMessage());

            return self::FAILURE;
        }
    }

    /**
     * @param array<array-key, array{source: string|null, scope:string|null, locale: string|null}> $productMapping
     * @throws \Doctrine\DBAL\Exception
     */
    private function setCatalogProductMapping(string $id, array $productMapping): void
    {
        $this->connection->executeQuery(
            'UPDATE akeneo_catalog SET product_mapping = :productMapping WHERE id = :id',
            [
                'id' => Uuid::fromString($id)->getBytes(),
                'productMapping' => $productMapping,
            ],
            [
                'productMapping' => Types::JSON,
            ],
        );
    }

    private function getProductMappingSchemaRaw(): string
    {
        return <<<'JSON_WRAP'
        {
          "$id": "https://example.com/product",
          "$schema": "https://api.akeneo.com/mapping/product/0.0.4/schema",
          "$comment": "My first schema !",
          "title": "Product Mapping",
          "description": "JSON Schema describing the structure of products expected by our application",
          "type": "object",
          "properties": {
            "uuid": {
              "type": "string"
            },
            "name": {
              "type": "string",
              "description": "A word or phrase that best describes the product. This will help Amazon.com locate the product when customers perform searches on our site. This is in addition to the valid values that you must submit for your product. It is in your best interest to fill in all search terms."
            },
            "description": {
              "type": "string"
            },
            "erp_name": {
              "type": "string",
              "title": "Erp name"
            },
            "meta_title": {
              "type": "string",
              "title": "Meta title"
            }
          }
        }
        JSON_WRAP;
    }
}
