<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema\Tests;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;
use PHPUnit\Framework\Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Version_6_0_20211028125410_add_type_to_connection_Integration extends TestCase
{
    use ExecuteMigrationTrait;

    private const MIGRATION_LABEL = '_6_0_20211028125410_add_type_to_connection';

    private Connection $connection;
    private ValidatorInterface $validator;
    private SimpleFactoryInterface $userFactory;
    private ObjectUpdaterInterface $userUpdater;
    private SaverInterface $userSaver;

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection = $this->get('database_connection');
        $this->validator = $this->get('validator');
        $this->userFactory = $this->get('pim_user.factory.user');
        $this->userUpdater = $this->get('pim_user.updater.user');
        $this->userSaver = $this->get('pim_user.saver.user');
    }

    public function test_it_adds_a_new_type_column_to_the_connection_table(): void
    {
        $this->dropTypeIfExists();

        $this->createConnection('connectionA');
        $this->createConnection('connectionB');

        $this->reExecuteMigration(self::MIGRATION_LABEL);
        Assert::assertEquals(true, $this->typeColumnExists());

        $allConnectionTypes = $this->getAllConnectionTypes();

        Assert::assertEquals([
            'connectionA' => 'default',
            'connectionB' => 'default',
        ], $allConnectionTypes);
    }

    private function dropTypeIfExists(): void
    {
        if ($this->typeColumnExists()) {
            $this->connection->executeQuery('ALTER TABLE akeneo_connectivity_connection DROP COLUMN type;');
        }

        Assert::assertEquals(false, $this->typeColumnExists());
    }

    private function typeColumnExists(): bool
    {
        $columns = $this->connection->getSchemaManager()->listTableColumns('akeneo_connectivity_connection');

        return isset($columns['type']);
    }

    private function createConnection(string $label): void
    {
        $user = $this->createUser([
            'username' => 'user_' . $label,
            'password' => 'dvx9bjw5b923',
            'email' => $label . '_email@email.com',
        ]);

        $clientId = $this->createClient($label);

        $data = [
            'client_id' => $clientId,
            'user_id' => $user->getId(),
            'code' => $label,
            'label' => $label,
            'flow_type' => 'test_flow_type',
            'image' => null,
            'auditable' => false,
        ];

        $this->connection->insert('akeneo_connectivity_connection', $data, ['auditable' => Types::BOOLEAN]);
    }

    private function createUser(array $data): UserInterface
    {
        $user = $this->userFactory->create();
        $this->userUpdater->update($user, $data);
        $this->userSaver->save($user);

        return $user;
    }

    private function createClient(string $label): string
    {
        $this->connection->insert(
            'pim_api_client',
            [
                'label' => $label,
                'random_id' => $label,
                'secret' => $label,
                'allowed_grant_types' => [],
                'redirect_uris' => [],
            ],
            [
                'allowed_grant_types' => Types::ARRAY,
                'redirect_uris' => Types::ARRAY
            ]
        );

        return $this->connection->lastInsertId();
    }

    private function getAllConnectionTypes(): array
    {
        $query = 'SELECT code, type FROM akeneo_connectivity_connection';

        $data = $this->connection->executeQuery($query)->fetchAll();

        return array_column($data, 'type', 'code');
    }
}
