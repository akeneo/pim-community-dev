<?php

declare(strict_types=1);

namespace AkeneoTestEnterprise\Pim\Permission\Integration\Persistence\ORM\Locale;

use Akeneo\Channel\Component\Repository\LocaleRepositoryInterface;
use Akeneo\Pim\Permission\Bundle\Persistence\ORM\Locale\GetLocalesAccessesWithHighestLevel;
use Akeneo\Pim\Permission\Component\Attributes;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\UserManagement\Component\Repository\GroupRepositoryInterface;
use Doctrine\DBAL\Connection;

class GetLocalesAccessesWithHighestLevelIntegration extends TestCase
{
    private GetLocalesAccessesWithHighestLevel $query;
    private GroupRepositoryInterface $groupRepository;
    private LocaleRepositoryInterface $localeRepository;
    private Connection $connection;

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

        $this->query = $this->get(GetLocalesAccessesWithHighestLevel::class);
        $this->groupRepository = $this->get('pim_user.repository.group');
        $this->localeRepository = $this->get('pim_catalog.repository.locale');
        $this->connection = $this->get('database_connection');
    }

    public function testItFetchesLocalesHighestAccessLevel(): void
    {
        $groupId = $this->groupRepository->findOneByIdentifier('redactor')->getId();

        $this->grantLocalePermission($groupId, 'fr_FR', Attributes::EDIT_ITEMS);
        $this->grantLocalePermission($groupId, 'en_US', Attributes::VIEW_ITEMS);

        $expected = [
            'fr_FR' => Attributes::EDIT_ITEMS,
            'en_US' => Attributes::VIEW_ITEMS,
        ];

        $results = $this->query->execute($groupId);

        $this->assertEquals($expected, $results);
    }

    private function grantLocalePermission(int $groupId, string $localeCode, string $permission): void
    {
        $localeId = $this->localeRepository->findOneByIdentifier($localeCode)->getId();

        $canView = 1;
        $canEdit = $permission === Attributes::EDIT_ITEMS ? 1 : 0;

        $this->connection->insert('pimee_security_locale_access', [
            'user_group_id' => $groupId,
            'locale_id' => $localeId,
            'view_products' => $canView,
            'edit_products' => $canEdit,
        ]);
    }
}
