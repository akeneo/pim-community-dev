<?php

declare(strict_types=1);

namespace AkeneoTestEnterprise\Pim\Permission\Integration\Persistence\ORM\Locale;

use Akeneo\Channel\Component\Repository\LocaleRepositoryInterface;
use Akeneo\Pim\Permission\Bundle\Persistence\ORM\Locale\GetLocalesAccessesWithHighestLevel;
use Akeneo\Pim\Permission\Component\Attributes;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\UserManagement\Component\Repository\GroupRepositoryInterface;
use AkeneoTestEnterprise\Pim\Permission\FixturesLoader\LocalePermissionsFixturesLoader;
use Doctrine\DBAL\Connection;

class GetLocalesAccessesWithHighestLevelIntegration extends TestCase
{
    private GetLocalesAccessesWithHighestLevel $query;
    private GroupRepositoryInterface $groupRepository;
    private LocalePermissionsFixturesLoader $localePermissionsFixturesLoader;

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
        $this->localePermissionsFixturesLoader = $this->get('akeneo_integration_tests.loader.locale_permissions');
    }

    public function testItFetchesLocalesHighestAccessLevel(): void
    {
        $group = $this->groupRepository->findOneByIdentifier('redactor');

        $this->localePermissionsFixturesLoader->givenTheRightOnLocaleCodes(Attributes::EDIT_ITEMS, $group, ['fr_FR']);
        $this->localePermissionsFixturesLoader->givenTheRightOnLocaleCodes(Attributes::VIEW_ITEMS, $group, ['en_US']);

        $expected = [
            'fr_FR' => Attributes::EDIT_ITEMS,
            'en_US' => Attributes::VIEW_ITEMS,
        ];

        $results = $this->query->execute($group->getId());

        $this->assertEquals($expected, $results);
    }
}
