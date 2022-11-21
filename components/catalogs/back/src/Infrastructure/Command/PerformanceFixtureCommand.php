<?php
declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Command;

use Akeneo\Catalogs\Application\Persistence\Locale\GetLocalesQueryInterface;
use Akeneo\Catalogs\ServiceAPI\Command\CreateCatalogCommand;
use Akeneo\Catalogs\ServiceAPI\Command\UpdateProductMappingSchemaCommand;
use Akeneo\Catalogs\ServiceAPI\Messenger\CommandBus;
use Akeneo\Channel\Infrastructure\Component\Model\ChannelInterface;
use Akeneo\Connectivity\Connection\ServiceApi\Service\ConnectedAppFactory;
use Akeneo\Connectivity\Connection\ServiceApi\Service\ConnectedAppRemover;
use Akeneo\Pim\Enrichment\Component\Product\Model\AbstractProduct;
use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetIdentifierValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetTextValue;
use Akeneo\Pim\Enrichment\Product\API\ValueObject\ProductIdentifier;
use Akeneo\Pim\Enrichment\Product\API\ValueObject\ProductUuid;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeOptionInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeOptionValue;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Akeneo\UserManagement\Component\Repository\UserRepositoryInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PerformanceFixtureCommand extends Command implements ContainerAwareInterface
{
    private const NUMBER_OF_PRODUCTS = 100;
    private const NUMBER_OF_MAPPED_ATTRIBUTES = 100;

    protected static $defaultName = 'akeneo:catalogs:performance-fixtures';
    protected static $defaultDescription = 'Do not run this command in production env. Installs fixtures for dev only.';

    public function __construct(
        private ContainerInterface $container,
        private ConnectedAppFactory $connectedAppFactory,
        private CommandBus $commandBus,
        private UserRepositoryInterface $userRepository,
        private Connection $connection,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setHelp('This command allows you to create a catalog and the associated connected App.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->connection->beginTransaction();

        try {
            $userAdmin = $this->createUser('admin', ['IT support'], ['ROLE_ADMINISTRATOR']);

            $this->createChannel('print', ['en_US', 'fr_FR']);

            $productMappingSchemaTargets = [
                'uuid' => ['type' => 'string'],
            ];

            $productMappingTargetSourceAssociations = [
                'uuid' => [
                    'source' => 'uuid',
                    'scope' => null,
                    'locale' => null,
                ],
            ];

            for ($i = 0; $i < self::NUMBER_OF_MAPPED_ATTRIBUTES; $i++) {
                $targetCode = \sprintf('target_%d', $i);
                $sourceCode = \sprintf('source_%d', $i);

                $this->createAttribute([
                    'code' => $sourceCode,
                    'type' => 'pim_catalog_text',
                    'scopable' => true,
                    'localizable' => true,
                ]);

                // create targets for the product mapping schema
                $productMappingSchemaTargets[$targetCode] = ['type' => 'string'];

                // create target/source associations for the product mapping
                $productMappingTargetSourceAssociations[$targetCode] = [
                    'source' => $sourceCode,
                    'scope' => 'print',
                    'locale' => 'en_US',
                ];
            }

            for ($i = 0; $i < self::NUMBER_OF_PRODUCTS; $i++) {
                $attributes = [];
                for ($j = 0; $j < self::NUMBER_OF_MAPPED_ATTRIBUTES; $j++) {
                    $attributes[] = new SetTextValue(
                        \sprintf('source_%d', $j),
                        'print',
                        'en_US',
                        \sprintf('value_%d_%d', $i, $j),
                    );
                }
                $this->createProduct(Uuid::uuid4(), $attributes, $userAdmin->getId());
            }

            $connectedApp = $this->connectedAppFactory->createFakeConnectedAppWithValidToken(
                '555d7447-2dab-474e-9026-f5d33c401b74',
                'shopifi',
                [
                    'read_catalogs',
                    'write_catalogs',
                    'delete_catalogs',
                    'read_products',
                ]
            );

            /** @var UserInterface|null $user */
            $user = $this->userRepository->findOneBy(['username' => $connectedApp->getUsername()]);
            \assert(null !== $user);

            $catalogWithMappingId = 'db1079b6-f397-4a6a-bae4-8658e64ad47c';

            $this->commandBus->execute(new CreateCatalogCommand(
                $catalogWithMappingId,
                'Catalog with Mapping',
                $user->getUserIdentifier(),
            ));

            $this->enableCatalog($catalogWithMappingId);

            $this->setCatalogProductMapping($catalogWithMappingId, $productMappingTargetSourceAssociations);

            $this->commandBus->execute(new UpdateProductMappingSchemaCommand(
                $catalogWithMappingId,
                \json_decode($this->getProductMappingSchemaRaw($productMappingSchemaTargets), false, 512, JSON_THROW_ON_ERROR),
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
     *
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
            ]
        );
    }

    private function getProductMappingSchemaRaw(array $productMappingSchemaTargets): string
    {
        return \json_encode([
            '$id' => 'https://example.com/product',
            '$schema' => 'https://api.akeneo.com/mapping/product/0.0.2/schema',
            '$comment' => 'My first schema !',
            'title' => 'Product Mapping',
            'description' => 'JSON Schema describing the structure of products expected by our application',
            'type' => 'object',
            'properties' => $productMappingSchemaTargets
        ], JSON_THROW_ON_ERROR);
    }

    protected function createChannel(string $code, array $locales = [], array $currencies = ['USD']): void
    {
        /** @var ChannelInterface $channel */
        $channel = $this->container->get('pim_catalog.factory.channel')->create();
        $this->container->get('pim_catalog.updater.channel')->update($channel, [
            'code' => $code,
            'locales' => $locales,
            'currencies' => $currencies,
            'category_tree' => 'master',
        ]);
        $this->container->get('pim_catalog.saver.channel')->save($channel);
    }

    protected function createAttribute(array $data): void
    {
        $data = \array_merge([
            'group' => 'other',
        ], $data);

        $options = $data['options'] ?? [];
        unset($data['options']);

        $attribute = $this->container->get('pim_catalog.factory.attribute')->create();
        $this->container->get('pim_catalog.updater.attribute')->update($attribute, $data);
        $this->container->get('pim_catalog.saver.attribute')->save($attribute);

        if ([] !== $options) {
            $this->createAttributeOptions($attribute, $options);
        }
    }

    private function createAttributeOptions(AttributeInterface $attribute, array $codes): void
    {
        $factory = $this->container->get('pim_catalog.factory.attribute_option');
        $locales = \array_map(
            static fn ($locale) => $locale['code'],
            $this->container->get(GetLocalesQueryInterface::class)->execute()
        );

        $options = [];

        foreach ($codes as $i => $code) {
            /** @var AttributeOptionInterface $option */
            $option = $factory->create();
            $option->setCode(\strtolower(\trim(\preg_replace('/[^A-Za-z0-9-]+/', '_', $code))));
            $option->setAttribute($attribute);
            $option->setSortOrder($i);

            foreach ($locales as $locale) {
                $value = new AttributeOptionValue();
                $value->setOption($option);
                $value->setLocale($locale);
                $value->setLabel($code);

                $option->addOptionValue($value);
            }

            $options[] = $option;
        }

        $this->container->get('pim_catalog.saver.attribute_option')->saveAll($options);
    }

    protected function createProduct(string|UuidInterface $identifier, array $intents = [], ?int $userId = null): AbstractProduct
    {
        $bus = $this->container->get('pim_enrich.product.message_bus');

        if (null === $userId) {
            $user = $this->container->get('security.token_storage')->getToken()?->getUser();
            \assert($user instanceof UserInterface);
            $userId = $user->getId();
        }

        Assert::notNull($userId);

        $command = \is_string($identifier) ?
            UpsertProductCommand::createWithIdentifier(
                $userId,
                ProductIdentifier::fromIdentifier($identifier),
                $intents,
            ) :
            UpsertProductCommand::createWithUuid(
                $userId,
                ProductUuid::fromUuid($identifier),
                \array_merge(
                    [
                        new SetIdentifierValue('sku', $identifier->toString()),
                    ],
                    $intents,
                ),
            );

        $bus->dispatch($command);

        $this->container->get('akeneo_elasticsearch.client.product_and_product_model')->refreshIndex();

        return $this->container->get('pim_catalog.repository.product')->findOneByIdentifier($identifier);
    }

    protected function createUser(string $username, ?array $groups = null, ?array $roles = null): UserInterface
    {
        $userPayload = [
            'username' => $username,
            'password' => \random_int(0, \mt_getrandmax()),
            'first_name' => 'firstname_' . \random_int(0, \mt_getrandmax()),
            'last_name' => 'lastname_' . \random_int(0, \mt_getrandmax()),
            'email' => \sprintf('%s@example.com', $username),
        ];

        if (null !== $groups) {
            $userPayload['groups'] = $groups;
        }

        if (null !== $roles) {
            $userPayload['roles'] = $roles;
        }

        $user = $this->container->get('pim_user.factory.user')->create();
        $this->container->get('pim_user.updater.user')->update($user, $userPayload);

        $violations = $this->container->get('validator')->validate($user);
        Assert::count($violations, 0);

        $this->container->get('pim_user.saver.user')->save($user);

        return $user;
    }

    protected function enableCatalog(string $id): void
    {
        $this->connection->executeQuery(
            'UPDATE akeneo_catalog SET is_enabled = 1 WHERE id = :id',
            [
                'id' => Uuid::fromString($id)->getBytes(),
            ]
        );
    }

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }
}
