<?php

declare(strict_types=1);

namespace AkeneoTestEnterprise\Pim\Permission\Integration\Persistence\ORM;

use Akeneo\Pim\Permission\Bundle\Persistence\ORM\Locale\GetUserGroupLocalesAccesses;
use Akeneo\Pim\Permission\Component\Attributes;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\UserManagement\Component\Model\GroupInterface;
use AkeneoTestEnterprise\Pim\Permission\FixturesLoader\LocaleFixturesLoader;
use AkeneoTestEnterprise\Pim\Permission\FixturesLoader\LocalePermissionsFixturesLoader;
use AkeneoTestEnterprise\Pim\Permission\FixturesLoader\UserGroupPermissionsFixturesLoader;

class GetUserGroupLocalesAccessesIntegration extends TestCase
{
    private GetUserGroupLocalesAccesses $query;
    private LocalePermissionsFixturesLoader $localePermissionsFixturesLoader;
    private UserGroupPermissionsFixturesLoader $userGroupPermissionsFixturesLoader;
    private GroupInterface $redactorUserGroup;

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->query = $this->get(GetUserGroupLocalesAccesses::class);

        $this->localePermissionsFixturesLoader = $this->get('akeneo_integration_tests.loader.locale_permissions');
        $this->userGroupPermissionsFixturesLoader = $this->get('akeneo_integration_tests.loader.user_group_permissions');

        $adminUser = $this->createAdminUser();
        $this->redactorUserGroup = $this->get('pim_user.repository.group')->findOneByIdentifier('redactor');
        $adminUser->addGroup($this->redactorUserGroup);

        /** @var LocaleFixturesLoader $localeFixturesLoader */
        $localeFixturesLoader = $this->get('akeneo_integration_tests.loader.locale');

        $ecommerceChannel = $this->get('pim_catalog.repository.channel')->findOneByIdentifier('ecommerce');

        $localeFixturesLoader->createLocale(['code' => 'locale_A'], $ecommerceChannel);
        $localeFixturesLoader->createLocale(['code' => 'locale_B'], $ecommerceChannel);
        $localeFixturesLoader->createLocale(['code' => 'locale_C'], $ecommerceChannel);
        $localeFixturesLoader->createLocale(['code' => 'locale_D']); // not activated
    }

    public function localePermissionsDataProvider(): array
    {
        return [
            'test without permissions' => [
                'expected' => [
                    'edit' => [
                        'all' => false,
                        'identifiers' => [],
                    ],
                    'view' => [
                        'all' => false,
                        'identifiers' => [],
                    ],
                ],
                'userGroupDefaultPermissions' => [],
                'viewableLocales' => [],
                'editableLocales' => [],
            ],
            'test with not activated locale' => [
                'expected' => [
                    'edit' => [
                        'all' => false,
                        'identifiers' => ['locale_A'],
                    ],
                    'view' => [
                        'all' => false,
                        'identifiers' => ['locale_A'],
                    ],
                ],
                'userGroupDefaultPermissions' => [],
                'viewableLocales' => ['locale_A', 'locale_D'],
                'editableLocales' => ['locale_A', 'locale_D'],
            ],
            'test it returns "all" flag and locales for each access level' => [
                'expected' => [
                    'edit' => [
                        'all' => false,
                        'identifiers' => ['locale_A', 'locale_C'],
                    ],
                    'view' => [
                        'all' => true,
                        'identifiers' => [],
                    ],
                ],
                'userGroupDefaultPermissions' => [
                    'locale_edit' => false,
                    'locale_view' => true,
                ],
                'editableLocales' => ['locale_A', 'locale_C'],
                'viewableLocales' => ['locale_A', 'locale_B', 'locale_C'],
            ],
        ];
    }

    /**
     * @dataProvider localePermissionsDataProvider
     */
    public function testItFetchesUserGroupLocalesAccesses(
        array $expected,
        array $userGroupDefaultPermissions,
        array $editableLocales,
        array $viewableLocales
    ): void {
        $this->localePermissionsFixturesLoader->givenTheRightOnLocaleCodes(Attributes::VIEW_ITEMS, $this->redactorUserGroup, $viewableLocales);
        $this->localePermissionsFixturesLoader->givenTheRightOnLocaleCodes(Attributes::EDIT_ITEMS, $this->redactorUserGroup, $editableLocales);
        $this->userGroupPermissionsFixturesLoader->givenTheUserGroupDefaultPermissions($this->redactorUserGroup, $userGroupDefaultPermissions);

        $results = $this->query->execute($this->redactorUserGroup->getName());

        $this->assertSame($expected, $results);
    }
}
