<?php

declare(strict_types=1);

namespace AkeneoTestEnterprise\Pim\Permission\EndToEnd\InternalApi;

use Akeneo\Channel\Infrastructure\Component\Repository\ChannelRepositoryInterface;
use Akeneo\Pim\Permission\Component\Attributes;
use Akeneo\Test\Integration\Configuration;
use Akeneo\UserManagement\Component\Repository\GroupRepositoryInterface;
use AkeneoTestEnterprise\Pim\Permission\FixturesLoader\LocaleFixturesLoader;
use AkeneoTestEnterprise\Pim\Permission\FixturesLoader\LocalePermissionsFixturesLoader;
use AkeneoTestEnterprise\Pim\Permission\FixturesLoader\UserGroupPermissionsFixturesLoader;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;

class GetUserGroupLocalesPermissionsActionEndToEnd extends WebTestCase
{
    private LocaleFixturesLoader $localeFixturesLoader;
    private LocalePermissionsFixturesLoader $localePermissionsFixturesLoader;
    private UserGroupPermissionsFixturesLoader $userGroupPermissionsFixturesLoader;
    private GroupRepositoryInterface $groupRepository;
    private ChannelRepositoryInterface $channelRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->localeFixturesLoader = $this->get('akeneo_integration_tests.loader.locale');
        $this->localePermissionsFixturesLoader = $this->get('akeneo_integration_tests.loader.locale_permissions');
        $this->userGroupPermissionsFixturesLoader = $this->get('akeneo_integration_tests.loader.user_group_permissions');
        $this->groupRepository = $this->get('pim_user.repository.group');
        $this->channelRepository = $this->get('pim_catalog.repository.channel');
    }

    public function testItReturnsUserGroupLocalePermissions(): void
    {
        $adminUser = $this->authenticateAsAdmin();
        $redactorUserGroup = $this->groupRepository->findOneByIdentifier('Redactor');
        $adminUser->addGroup($redactorUserGroup);

        $ecommerceChannel = $this->channelRepository->findOneByIdentifier('ecommerce');

        $this->localeFixturesLoader->createLocale(['code' => 'locale_A'], $ecommerceChannel);
        $this->localeFixturesLoader->createLocale(['code' => 'locale_B'], $ecommerceChannel);
        $this->localeFixturesLoader->createLocale(['code' => 'locale_C'], $ecommerceChannel);
        $this->localeFixturesLoader->createLocale(['code' => 'locale_D']); // not activated

        $this->localePermissionsFixturesLoader->givenTheRightOnLocaleCodes(Attributes::VIEW_ITEMS, $redactorUserGroup, [
            'locale_A',
            'locale_B',
            'locale_C',
        ]);
        $this->localePermissionsFixturesLoader->givenTheRightOnLocaleCodes(Attributes::EDIT_ITEMS, $redactorUserGroup, [
            'locale_A',
            'locale_C',
            'locale_D',
        ]);
        $this->userGroupPermissionsFixturesLoader->givenTheUserGroupDefaultPermissions($redactorUserGroup, [
            'locale_edit' => false,
            'locale_view' => true,
        ]);

        $this->client->request(
            'GET',
            '/rest/permissions/user-group/Redactor/locale',
            [],
            [],
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            ]
        );

        Assert::assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        $result = json_decode($this->client->getResponse()->getContent(), true);

        Assert::assertSame([
            'edit' => [
                'all' => false,
                'identifiers' => ['locale_A', 'locale_C'],
            ],
            'view' => [
                'all' => true,
                'identifiers' => [],
            ],
         ], $result);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
