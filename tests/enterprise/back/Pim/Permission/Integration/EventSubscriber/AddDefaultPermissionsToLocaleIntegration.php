<?php

declare(strict_types=1);

namespace AkeneoTestEnterprise\Pim\Permission\Integration\EventSubscriber;

use Akeneo\Channel\Infrastructure\Component\Model\Locale;
use Akeneo\Channel\Infrastructure\Component\Model\Channel;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\UserManagement\Bundle\Doctrine\ORM\Repository\GroupRepository;
use Akeneo\UserManagement\Component\Model\Group;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;

class AddDefaultPermissionsToLocaleIntegration extends TestCase
{
    private Connection $connection;
    private GroupRepository $groupRepository;

    public function setUp(): void
    {
        parent::setUp();

        $this->connection = self::getContainer()->get('database_connection');
        $this->groupRepository = self::getContainer()->get('pim_user.repository.group');
    }

    public function testDefaultUserGroupHasPermissionsOnNewLocalesByDefault()
    {
        /** @var Group $defaultUserGroup */
        $defaultUserGroup = $this->groupRepository->getDefaultUserGroup();
        assert($defaultUserGroup !== null);

        $locale = $this->createLocale('foo_FOO');
        $this->activateLocaleForEcommerceChannel($locale);

        $permissions = $this->getLocalePermissions($defaultUserGroup->getId(), $locale->getId());
        $this->assertEquals([
            'view' => true,
            'edit' => true,
        ], $permissions);
    }

    /**
     * @dataProvider permissions
     */
    public function testUserGroupHasExpectedPermissionsOnNewLocalesByDefault(
        array $defaultPermissions,
        array $expectedPermissions
    ) {
        $userGroup = $this->createUserGroup('foo', $defaultPermissions);
        $locale = $this->createLocale('foo_FOO');
        $this->activateLocaleForEcommerceChannel($locale);

        $permissions = $this->getLocalePermissions(
            $userGroup->getId(),
            $locale->getId()
        );
        $this->assertEquals($expectedPermissions, $permissions);
    }

    /**
     * @dataProvider permissions
     */
    public function testUserGroupHasExpectedPermissionsByDefaultWhenALocaleIsAddedToAChannel(
        array $defaultPermissions,
        array $expectedPermissions
    ): void {
        $userGroup = $this->createUserGroup('foo', $defaultPermissions);

        $locale = $this->get('pim_catalog.repository.locale')->findOneByIdentifier('fr_FR');
        $channel = $this->get('pim_catalog.repository.channel')->findOneByIdentifier('ecommerce');
        assert(!$channel->hasLocale($locale));

        $channel->addLocale($locale);

        $this->get('pim_catalog.saver.channel')->save($channel);

        $permissions = $this->getLocalePermissions(
            $userGroup->getId(),
            $locale->getId()
        );
        $this->assertEquals($expectedPermissions, $permissions);
    }

    public function permissions(): array
    {
        return [
            [
                [
                    'locale_view' => true,
                    'locale_edit' => true,
                ],
                [
                    'view' => true,
                    'edit' => true,
                ],
            ],
            [
                [
                    'locale_view' => true,
                    'locale_edit' => false,
                ],
                [
                    'view' => true,
                    'edit' => false,
                ],
            ],
        ];
    }

    /**
     * @return array{view: bool, edit: bool}|null
     */
    private function getLocalePermissions(int $userGroupId, int $localeId): ?array
    {
        $query = <<<SQL
SELECT 
   view_products AS view,
   edit_products AS edit
FROM pimee_security_locale_access
WHERE user_group_id = :user_group_id
AND locale_id = :locale_id
SQL;

        $permissions = $this->connection->fetchAssoc($query, [
            'user_group_id' => $userGroupId,
            'locale_id' => $localeId,
        ]);

        if (!$permissions) {
            return null;
        }

        return array_map(fn($v) => (bool) $v, $permissions);
    }

    private function createUserGroup(string $name, array $defaultPermissions): Group
    {
        $userGroup = new Group();
        $userGroup->setName($name);
        $userGroup->setDefaultPermissions($defaultPermissions);

        /** @var EntityManagerInterface $em */
        $em = $this->get('doctrine.orm.entity_manager');
        $em->persist($userGroup);
        $em->flush();

        return $userGroup;
    }

    private function createLocale(string $code): Locale
    {
        $locale = new Locale();
        $locale->setCode($code);
        $this->get('pim_catalog.saver.locale')->save($locale);

        return $locale;
    }

    protected function activateLocaleForEcommerceChannel(Locale $locale): void
    {
        /** @var Channel $channel */
        $channel = $this->get('pim_catalog.repository.channel')->findOneByIdentifier('ecommerce');
        $channel->addLocale($locale);

        $this->get('pim_catalog.saver.channel')->save($channel);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog(featureFlags: ['permission']);
    }
}
