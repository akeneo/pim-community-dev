<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Integration\Apps;

use Akeneo\Connectivity\Connection\Infrastructure\Apps\AppRoleWithScopesFactory;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\UserManagement\Component\Model\RoleInterface;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AppRoleWithScopesFactoryIntegration extends TestCase
{
    private AppRoleWithScopesFactory $factory;
    private Connection $connection;
    private AccessDecisionManagerInterface $accessDecisionManager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->factory = $this->get(AppRoleWithScopesFactory::class);
        $this->connection = $this->get('database_connection');
        $this->accessDecisionManager = $this->get('security.access.decision_manager');
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    public function test_it_creates_a_role_with_permissions_derived_from_the_scopes(): void
    {
        $role = $this->factory->createRole('foo', [
            'read_products',
            'write_products',
        ]);

        Assert::assertInstanceOf(RoleInterface::class, $role);
        Assert::assertEquals('foo', $role->getLabel());

        $this->assertRoleIsPersisted($role);
        $this->assertRoleAcls($role->getRole(), [
            'pim_api_overall_access' => true,
            'pim_api_product_list' => true,
            'pim_api_product_edit' => true,
            'pim_api_product_remove' => false,
        ]);
    }

    private function assertRoleIsPersisted(RoleInterface $role): void
    {
        $query = <<<SQL
SELECT * FROM oro_access_role WHERE label = :label
SQL;
        $stmt = $this->connection->executeQuery($query, [
            'label' => $role->getLabel(),
        ]);

        $raw = $stmt->fetchAssociative();

        Assert::assertNotFalse($raw);

        Assert::assertArrayHasKey('role', $raw);
        Assert::assertEquals($role->getRole(), $raw['role']);
        Assert::assertArrayHasKey('label', $raw);
        Assert::assertEquals($role->getLabel(), $raw['label']);
        Assert::assertArrayHasKey('type', $raw);
        Assert::assertEquals('app', $raw['type']);
    }

    private function assertRoleAcls(string $role, array $acls): void
    {
        $token = new UsernamePasswordToken('username', 'main', [$role]);

        foreach ($acls as $acl => $expectedValue) {
            \assert(\is_bool($expectedValue));

            $isAllowed = $this->accessDecisionManager->decide($token, ['EXECUTE'], new ObjectIdentity('action', $acl));
            $this->assertEquals($expectedValue, $isAllowed);
        }
    }
}
