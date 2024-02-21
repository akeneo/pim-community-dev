<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Integration\Apps\Command;

use Akeneo\Connectivity\Connection\Application\Apps\Command\RequestAppAuthorizationCommand;
use Akeneo\Connectivity\Connection\Application\Apps\Command\RequestAppAuthorizationHandler;
use Akeneo\Connectivity\Connection\Application\Apps\Command\UpdateConnectedAppScopesWithAuthorizationCommand;
use Akeneo\Connectivity\Connection\Application\Apps\Command\UpdateConnectedAppScopesWithAuthorizationHandler;
use Akeneo\Connectivity\Connection\Domain\Apps\Exception\InvalidAppAuthorizationRequestException;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\Persistence\FindOneConnectedAppByIdQuery;
use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\ConnectedAppLoader;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UpdateConnectedAppScopesWithAuthorizationHandlerIntegration extends TestCase
{
    private UpdateConnectedAppScopesWithAuthorizationHandler $handler;
    private RequestAppAuthorizationHandler $appAuthorizationHandler;
    private FindOneConnectedAppByIdQuery $findOneConnectedAppByIdQuery;
    private ConnectedAppLoader $connectedAppLoader;
    private AccessDecisionManagerInterface $accessDecisionManager;

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->handler = $this->get(UpdateConnectedAppScopesWithAuthorizationHandler::class);
        $this->appAuthorizationHandler = $this->get(RequestAppAuthorizationHandler::class);
        $this->findOneConnectedAppByIdQuery = $this->get(FindOneConnectedAppByIdQuery::class);
        $this->connectedAppLoader = $this->get('akeneo_connectivity.connection.fixtures.connected_app_loader');
        $this->accessDecisionManager = $this->get('security.access.decision_manager');
    }

    public function throwExceptionDataProvider(): array
    {
        return [
            'not blank' => [
                '',
                'akeneo_connectivity.connection.connect.apps.constraint.client_id.not_blank',
            ],
            'client id must be valid' => [
                'unknownId',
                'akeneo_connectivity.connection.connect.apps.constraint.client_id.must_be_valid',
            ],
            'client id must have an ongoing authorization' => [
                '0dfce574-2238-4b13-b8cc-8d257ce7645b',
                'akeneo_connectivity.connection.connect.apps.constraint.client_id.must_have_ongoing_authorization',
            ],
        ];
    }

    public function scopesDataProvider(): array
    {
        return [
            'more scopes' => [
                ['read_products', 'write_categories'],
                ['delete_products', 'read_association_types', 'write_catalog_structure'],
                [
                    'pim_api_overall_access' => true,
                    //Delete products
                    'pim_api_product_list' => true,
                    'pim_api_product_edit' => true,
                    'pim_api_product_remove' => true,

                    //Read association types
                    'pim_api_association_type_list' => true,
                    'pim_api_association_type_edit' => false,

                    //Write catalog structure
                    'pim_api_attribute_edit' => true,
                    'pim_api_attribute_group_edit' => true,
                    'pim_api_family_edit' => true,
                    'pim_api_family_variant_edit' => true,

                    //No categories access
                    'pim_api_category_list' => false,
                    'pim_api_category_edit' => false,
                ],
            ],
            'less scopes' => [
                ['delete_products', 'read_association_types', 'write_catalog_structure'],
                ['read_products', 'write_categories'],
                [
                    'pim_api_overall_access' => true,

                    //Read products
                    'pim_api_product_list' => true,
                    'pim_api_product_edit' => false,
                    'pim_api_product_remove' => false,

                    //No association types access
                    'pim_api_association_type_list' => false,
                    'pim_api_association_type_edit' => false,

                    //Write catalog structure
                    'pim_api_attribute_edit' => false,
                    'pim_api_attribute_group_edit' => false,
                    'pim_api_family_edit' => false,
                    'pim_api_family_variant_edit' => false,

                    //Write categories access
                    'pim_api_category_list' => true,
                    'pim_api_category_edit' => true,
                ],
            ],
            'same scopes' => [
                ['read_products', 'write_categories'],
                ['read_products', 'write_categories'],
                [
                    'pim_api_overall_access' => true,

                    //Read products
                    'pim_api_product_list' => true,
                    'pim_api_product_edit' => false,
                    'pim_api_product_remove' => false,

                    //Write categories access
                    'pim_api_category_list' => true,
                    'pim_api_category_edit' => true,
                ],
            ],
        ];
    }

    /**
     * @dataProvider throwExceptionDataProvider
     */
    public function test_it_throws_when_the_command_is_not_valid(string $clientId, string $expectedMessage): void
    {
        $appId = '0dfce574-2238-4b13-b8cc-8d257ce7645b';

        $this->connectedAppLoader->createConnectedAppWithUserAndTokens(
            $appId,
            'akeneo_print',
        );

        $command = new UpdateConnectedAppScopesWithAuthorizationCommand($clientId);

        $this->expectException(InvalidAppAuthorizationRequestException::class);
        $this->expectExceptionMessage($expectedMessage);
        $this->handler->handle($command);
    }

    /**
     * @dataProvider scopesDataProvider
     */
    public function test_it_updates_scopes(array $oldScopes, array $newScopes, array $expectedAcls): void
    {
        $appId = '0dfce574-2238-4b13-b8cc-8d257ce7645b';

        $this->connectedAppLoader->createConnectedAppWithUserAndTokens(
            $appId,
            'akeneo_print',
            $oldScopes
        );

        $this->appAuthorizationHandler->handle(new RequestAppAuthorizationCommand(
            $appId,
            'code',
            \implode(' ', $newScopes),
            'http://anyurl.test'
        ));

        $foundApp = $this->findOneConnectedAppByIdQuery->execute($appId);
        Assert::assertEquals($oldScopes, $foundApp->getScopes());

        $this->handler->handle(new UpdateConnectedAppScopesWithAuthorizationCommand($appId));

        $foundApp = $this->findOneConnectedAppByIdQuery->execute($appId);
        Assert::assertEquals($newScopes, $foundApp->getScopes());
        $this->assertRoleIsUpdatedWithAcls('ROLE_AKENEO_PRINT', $expectedAcls);
    }

    private function assertRoleIsUpdatedWithAcls(string $role, array $acls): void
    {
        $token = new UsernamePasswordToken('username', 'main', [$role]);

        foreach ($acls as $acl => $expectedValue) {
            \assert(\is_bool($expectedValue));

            $isAllowed = $this->accessDecisionManager->decide($token, ['EXECUTE'], new ObjectIdentity('action', $acl));
            $this->assertEquals($expectedValue, $isAllowed, "$acl differs from expected value: $isAllowed");
        }
    }
}
