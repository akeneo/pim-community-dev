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
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Version_7_0_20220701154545_add_app_id_to_user_properties_Integration extends TestCase
{
    use ExecuteMigrationTrait;

    private const MIGRATION_LABEL = '_7_0_20220701154545_add_app_id_to_user_properties';

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

    public function test_it_adds_app_id_to_user_properties(): void
    {
        $uuid = '3cc771c0-2b73-464c-8451-bd072e239698';
        $this->createConnectedApp($uuid, 'connected_app_a');

        $this->reExecuteMigration(self::MIGRATION_LABEL);

        $this->assertUserPropertiesContainsAppId('user_connected_app_a', $uuid);
    }

    public function test_it_adds_app_id_to_user_properties_when_properties_already_exists(): void
    {
        $uuid = '1947c666-d042-4b41-893e-baa306565a20';
        $this->createConnectedApp($uuid, 'connected_app_b', ['some_existing_property' => 'some_value']);

        $this->reExecuteMigration(self::MIGRATION_LABEL);

        $this->assertUserPropertiesContainsAppId('user_connected_app_b', $uuid);
    }

    public function test_it_does_not_add_app_id_to_user_properties_when_it_is_not_an_app_user(): void
    {
        $userData = [
            'username' => 'user_c',
            'password' => 'dvx9bjw5b923',
            'email' => 'user_c_email@email.com',
        ];

        $this->createUser($userData);

        $this->reExecuteMigration(self::MIGRATION_LABEL);

        $this->assertUserPropertiesNotContainsAppId('user_c');
    }

    private function createConnectedApp(string $id, string $label, ?array $userProperties = null): void
    {
        $this->createConnection($label, $userProperties);

        $this->connection->insert('akeneo_connectivity_connected_app', [
            'id' => $id,
            'name' => $label,
            'logo' => $label,
            'author' => $label,
            'categories' => \json_encode([]),
            'scopes' => \json_encode([]),
            'connection_code' => $label,
            'user_group_name' => 'Manager',
        ]);
    }

    private function createConnection(string $label, ?array $userProperties = null): void
    {
        $userData = [
            'username' => 'user_' . $label,
            'password' => 'dvx9bjw5b923',
            'email' => $label . '_email@email.com',
        ];

        if (null !== $userProperties) {
            $userData['properties'] = $userProperties;
        }

        $user = $this->createUser($userData);

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

    private function assertUserPropertiesContainsAppId(string $username, string $appId): void
    {
        $sql = <<<SQL
            SELECT JSON_VALUE(oro_user.properties, "$.app_id") AS contains_app_id
            FROM oro_user 
            WHERE username = :username
        SQL;

        $containsAppId = $this->connection->fetchOne($sql, [
            'username' => $username,
        ]);

        Assert::assertEquals($appId, $containsAppId, 'User should have app id in its properties.');
    }

    private function assertUserPropertiesNotContainsAppId(string $username): void
    {
        $sql = <<<SQL
            SELECT JSON_CONTAINS_PATH(oro_user.properties, 'one', "$.app_id") AS contains_app_id
            FROM oro_user 
            WHERE username = :username
        SQL;

        $containsAppId = $this->connection->fetchOne($sql, [
            'username' => $username,
        ]);

        Assert::assertEquals(0, $containsAppId, 'User should not have app id in its properties.');
    }
}
