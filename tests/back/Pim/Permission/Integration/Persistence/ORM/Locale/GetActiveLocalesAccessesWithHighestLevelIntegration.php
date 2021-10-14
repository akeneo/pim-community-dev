<?php

declare(strict_types=1);

namespace AkeneoTestEnterprise\Pim\Permission\Integration\Persistence\ORM\Locale;

use Akeneo\Channel\Component\Repository\LocaleRepositoryInterface;
use Akeneo\Pim\Permission\Bundle\Persistence\ORM\Locale\GetActiveLocalesAccessesWithHighestLevel;
use Akeneo\Pim\Permission\Component\Attributes;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\UserManagement\Component\Repository\GroupRepositoryInterface;
use AkeneoTestEnterprise\Pim\Permission\FixturesLoader\LocalePermissionsFixturesLoader;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;

class GetActiveLocalesAccessesWithHighestLevelIntegration extends TestCase
{
    private GetActiveLocalesAccessesWithHighestLevel $query;
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

        $this->query = $this->get(GetActiveLocalesAccessesWithHighestLevel::class);
        $this->groupRepository = $this->get('pim_user.repository.group');
        $this->localePermissionsFixturesLoader = $this->get('akeneo_integration_tests.loader.locale_permissions');
    }

    public function testItFetchesLocalesHighestAccessLevel(): void
    {
        $group = $this->groupRepository->findOneByIdentifier('redactor');

        $this->activateLocales(['fr_FR', 'en_US']);

        $this->localePermissionsFixturesLoader->givenTheRightOnLocaleCodes(Attributes::EDIT_ITEMS, $group, ['fr_FR']);
        $this->localePermissionsFixturesLoader->givenTheRightOnLocaleCodes(Attributes::VIEW_ITEMS, $group, ['en_US']);

        $expected = [
            'fr_FR' => Attributes::EDIT_ITEMS,
            'en_US' => Attributes::VIEW_ITEMS,
        ];

        $results = $this->query->execute($group->getId());

        $this->assertEquals($expected, $results);
    }

    private function activateLocales(array $localeCodes): void
    {
        $channel = $this->get('pim_catalog.repository.channel')->findOneByIdentifier('ecommerce');
        $localeRepository = $this->get('pim_catalog.repository.locale');
        $localeSaver = $this->get('pim_catalog.saver.locale');

        foreach ($localeCodes as $localeCode) {
            $locale = $localeRepository->findOneByIdentifier($localeCode);
            $locale->addChannel($channel);

            $errors = $this->get('validator')->validate($locale);
            Assert::assertCount(0, $errors);
            $localeSaver->save($locale);
        }
    }
}
