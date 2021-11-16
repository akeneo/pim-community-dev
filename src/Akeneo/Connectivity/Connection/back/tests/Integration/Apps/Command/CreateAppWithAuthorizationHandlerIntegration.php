<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Integration\Apps\Command;

use Akeneo\Connectivity\Connection\Application\Apps\Command\CreateAppWithAuthorizationCommand;
use Akeneo\Connectivity\Connection\Application\Apps\Command\CreateAppWithAuthorizationHandler;
use Akeneo\Connectivity\Connection\Application\Apps\Command\RequestAppAuthorizationCommand;
use Akeneo\Connectivity\Connection\Application\Apps\Command\RequestAppAuthorizationHandler;
use Akeneo\Connectivity\Connection\Domain\Apps\Exception\InvalidAppAuthorizationRequest;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Connectivity\Connection\Domain\Settings\Persistence\Repository\ConnectionRepository;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\Persistence\DbalConnectedAppRepository;
use Akeneo\Connectivity\Connection\Tests\Integration\Mock\FakeWebMarketplaceApi;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Bundle\ApiBundle\Entity\Client;
use Akeneo\Tool\Bundle\ApiBundle\Security\ScopeMapper;
use Akeneo\UserManagement\Bundle\Doctrine\ORM\Repository\RoleWithPermissionsRepository;
use Akeneo\UserManagement\Bundle\Manager\UserManager;
use Akeneo\UserManagement\Component\Model\Group;
use Akeneo\UserManagement\Component\Model\User;
use Doctrine\Common\Collections\Collection;
use FOS\OAuthServerBundle\Model\ClientInterface;
use FOS\OAuthServerBundle\Model\ClientManagerInterface;
use PHPUnit\Framework\Assert;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CreateAppWithAuthorizationHandlerIntegration extends TestCase
{
    private CreateAppWithAuthorizationHandler $handler;
    private RequestAppAuthorizationHandler $appAuthorizationHandler;
    private ClientManagerInterface $clientManager;
    private UserManager $userManager;
    private PropertyAccessor $propertyAccessor;
    private FakeWebMarketplaceApi $webMarketplaceApi;
    private DbalConnectedAppRepository $appRepository;
    private ConnectionRepository $connectionRepository;
    private RoleWithPermissionsRepository $roleWithPermissionsRepository;
    private ScopeMapper $scopeMapper;

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->webMarketplaceApi = $this->get('akeneo_connectivity.connection.marketplace.web_marketplace_api');
        $this->handler = $this->get(CreateAppWithAuthorizationHandler::class);
        $this->appAuthorizationHandler = $this->get(RequestAppAuthorizationHandler::class);
        $this->clientManager = $this->get('fos_oauth_server.client_manager.default');
        $this->userManager = $this->get('pim_user.manager');
        $this->propertyAccessor = PropertyAccess::createPropertyAccessor();
        $this->appRepository = $this->get(DbalConnectedAppRepository::class);
        $this->connectionRepository = $this->get('akeneo_connectivity.connection.persistence.repository.connection');
        $this->roleWithPermissionsRepository = $this->get('pim_user.repository.role_with_permissions');
        $this->scopeMapper = $this->get('pim_api.security.scope_mapper');

        $this->loadAppsFixtures();

        $this->createOAuth2Client([
            'marketplacePublicAppId' => '90741597-54c5-48a1-98da-a68e7ee0a715',
        ]);

        $this->createOAuth2Client([
            'marketplacePublicAppId' => 'e4d35502-08c9-40b4-a378-05d4cb255862',
        ]);
    }

    private function loadAppsFixtures(): void
    {
        $apps = [
            [
                'id' => '90741597-54c5-48a1-98da-a68e7ee0a715',
                'name' => 'Akeneo Shopware 6 Connector by EIKONA Media',
                'logo' => 'https://marketplace.akeneo.com/sites/default/files/styles/extension_logo_large/public/extension-logos/akeneo-to-shopware6-eimed_0.jpg?itok=InguS-1N',
                'author' => 'EIKONA Media GmbH',
                'partner' => 'Akeneo Preferred Partner',
                'description' => 'With the new "Akeneo-Shopware-6-Connector" from EIKONA Media, you can smoothly export all your product data from Akeneo to Shopware. The connector uses the standard interfaces provided for data exchange. Benefit from up-to-date product data in all your e-commerce channels and be faster on the market.',
                'url' => 'https://marketplace.akeneo.com/extension/akeneo-shopware-6-connector-eikona-media',
                'categories' => [
                    'E-commerce',
                ],
                'certified' => false,
                'activate_url' => 'http://shopware.example.com/activate',
                'callback_url' => 'http://shopware.example.com/callback',
            ],
            [
                'id' => 'b18561ff-378e-41a5-babb-ca0ec0af569a',
                'name' => 'Akeneo PIM Connector for Shopify',
                'logo' => 'https://marketplace.akeneo.com/sites/default/files/styles/extension_logo_large/public/extension-logos/shopify-connector-logo-1200x.png?itok=mASOVlwC',
                'author' => 'StrikeTru',
                'partner' => 'Akeneo Partner',
                'description' => 'SaaS software from StrikeTru that seamlessly connects Akeneo PIM to the Shopify platform. It allows Shopify users to quickly setup a link to Akeneo PIM and sync all product catalog data to Shopify within minutes. It eliminates a lot of manual and repetitive work involved in updating the product catalog of a Shopify store. You can send and receive products, variations, modifiers, categories, standard and custom attributes, images and more from Akeneo PIM into your Shopify store. Compatible with all Akeneo PIM editions – Community, Growth, Enterprise (On-Premise, Cloud Flexibility, and Cloud Serenity) and StrikeTru\'s smallPIM.',
                'url' => 'https://marketplace.akeneo.com/extension/akeneo-pim-connector-shopify',
                'categories' => [
                    'E-commerce',
                ],
                'certified' => false,
                'activate_url' => 'http://shopify.example.com/activate',
                'callback_url' => 'http://shopify.example.com/callback',
            ],
        ];

        $this->webMarketplaceApi->setApps($apps);
    }

    private function createOAuth2Client(array $data): ClientInterface
    {
        $client = $this->clientManager->createClient();
        foreach ($data as $key => $value) {
            $this->propertyAccessor->setValue($client, $key, $value);
        }
        $this->clientManager->updateClient($client);

        return $client;
    }

    private function addAppAuthorization(string $clientId): void
    {
        $this->appAuthorizationHandler->handle(new RequestAppAuthorizationCommand(
            $clientId,
            'code',
            'write_catalog_structure delete_products read_association_types',
            'http://anyurl.test'
        ));
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
                'e4d35502-08c9-40b4-a378-05d4cb255862',
                'akeneo_connectivity.connection.connect.apps.constraint.client_id.must_have_ongoing_authorization',
            ],
        ];
    }

    /**
     * @dataProvider throwExceptionDataProvider
     */
    public function test_it_throws_when_the_command_is_not_valid(string $clientId, $expectedMessage)
    {
        $command = new CreateAppWithAuthorizationCommand($clientId);

        $this->expectException(InvalidAppAuthorizationRequest::class);
        $this->expectExceptionMessage($expectedMessage);
        $this->handler->handle($command);
    }

    public function test_it_handles_confirmation()
    {
        $appId = '90741597-54c5-48a1-98da-a68e7ee0a715';
        $appName = 'Akeneo Shopware 6 Connector by EIKONA Media';

        $this->addAppAuthorization($appId);

        $this->handler->handle(new CreateAppWithAuthorizationCommand($appId));

        $foundApp = $this->appRepository->findOneById($appId);
        Assert::assertNotNull($foundApp, 'No persisted app found');
        Assert::assertEquals($appId, $foundApp->getId());

        $foundConnection = $this->connectionRepository->findOneByCode($foundApp->getConnectionCode());
        Assert::assertNotNull($foundConnection, 'No persisted connection found');
        Assert::assertEquals(FlowType::OTHER, $foundConnection->flowType());
        Assert::assertEquals($appName, $foundConnection->label());
        Assert::assertEquals('app', $foundConnection->type());

        /** @var Client $foundClient */
        $foundClient = $this->clientManager->findClientBy(['id' => $foundConnection->clientId()->id()]);
        Assert::assertNotNull($foundClient, 'No persisted client found');
        Assert::assertEquals($appId, $foundClient->getMarketplacePublicAppId());

        $foundUser = $this->userManager->findUserBy(['id' => $foundConnection->userId()->id()]);
        Assert::assertNotNull($foundUser, 'No persisted user found');
        Assert::assertStringContainsString((string) $foundConnection->code(), $foundUser->getUsername(), 'User is not an app dedicated user');
        Assert::assertEquals($appName, $foundUser->getFullname());

        /** @var Collection $userGroups */
        $userGroups = $foundUser->getGroups();
        Assert::assertEquals(2, $userGroups->count(), 'User do not belong to exactly 2 groups');
        Assert::assertTrue($userGroups->exists(function (int $index, Group $group) {
            return $group->getName() === User::GROUP_DEFAULT;
        }), 'User do not have default user group');
        Assert::assertTrue($userGroups->exists(function (int $index, Group $group) {
            return $group->getType() === 'app' && $group->getName() !== User::GROUP_DEFAULT;
        }), 'The user group created is not of type "app"');

        $userRoles = $foundUser->getRoles();
        Assert::assertCount(1, $userRoles, 'User do not have exactly 1 role');
        $roleWithPermissions = $this->roleWithPermissionsRepository->findOneByIdentifier($userRoles[0]);
        Assert::assertNotNull($roleWithPermissions, 'No role with permissions found');

        $this->assertPermissionsGrantedFromScopes($roleWithPermissions->permissions(), $foundApp->getScopes());
    }

    private function assertPermissionsGrantedFromScopes(array $permissions, array $scopes)
    {
        foreach ($scopes as $scope) {
            $acls = $this->scopeMapper->getAcls($scope);
            foreach ($acls as $acl) {
                Assert::assertTrue($permissions["action:$acl"], "Missing ACL for $acl on scope $scope");
            }
        }
    }
}
