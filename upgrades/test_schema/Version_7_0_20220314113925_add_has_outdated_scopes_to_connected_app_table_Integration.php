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
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Version_7_0_20220314113925_add_has_outdated_scopes_to_connected_app_table_Integration extends TestCase
{
    use ExecuteMigrationTrait;

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

    public function test_it_adds_has_outdated_scopes_column_to_the_connected_app_table(): void
    {
        if ($this->hasOutdatedScopesColumnExists()) {
            $this->connection->executeQuery('ALTER TABLE akeneo_connectivity_connected_app DROP COLUMN has_outdated_scopes;');
        }

        self::assertFalse($this->hasOutdatedScopesColumnExists(), 'has_outdated_scopes column should not exist before migration');
        $this->createConnectedApp('connected_app_a');

        $this->reExecuteMigration('_7_0_20220314113925_add_has_outdated_scopes_to_connected_app_table');

        self::assertEquals(true, $this->hasOutdatedScopesColumnExists());
        self::assertFalse($this->connectedAppHasOutdatedScopes('connected_app_a'), 'Connected app should not have outdated scopes');
    }

    private function hasOutdatedScopesColumnExists(): bool
    {
        $columns = $this->connection->getSchemaManager()->listTableColumns('akeneo_connectivity_connected_app');

        return isset($columns['has_outdated_scopes']);
    }

    private function connectedAppHasOutdatedScopes(string $connectedAppId): bool
    {
        $query = 'SELECT has_outdated_scopes FROM akeneo_connectivity_connected_app WHERE id = :id';

        return (bool) $this->connection->fetchOne($query, ['id' => $connectedAppId]);
    }

    private function createConnectedApp(string $label): void
    {
        $this->createConnection($label);

        $this->connection->insert('akeneo_connectivity_connected_app', [
            'id' => $label,
            'name' => $label,
            'logo' => $label,
            'author' => $label,
            'categories' => \json_encode([]),
            'scopes' => \json_encode([]),
            'connection_code' => $label,
            'user_group_name' => 'Manager',
        ]);
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
}
