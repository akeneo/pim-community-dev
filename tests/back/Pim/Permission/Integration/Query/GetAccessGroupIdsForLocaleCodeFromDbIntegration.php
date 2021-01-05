<?php

declare(strict_types=1);

namespace AkeneoTestEnterprise\Pim\Permission\Integration\Query;

use Akeneo\Pim\Permission\Bundle\Entity\Query\GetAccessGroupIdsForLocaleCodeFromDb;
use Akeneo\Pim\Permission\Component\Attributes;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;

/**
 * @author Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 */
class GetAccessGroupIdsForLocaleCodeFromDbIntegration extends TestCase
{
    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }

    public function test_it_returns_group_ids_given_locale_and_access_level()
    {
        $groupIds = $this->getQuery()->getGrantedUserGroupIdsForLocaleCode('fr_FR', Attributes::EDIT_ITEMS);
        Assert::assertCount(2, $groupIds);
        Assert::assertContains('1', $groupIds);
        Assert::assertContains('2', $groupIds);

        $locale = $this->get('pim_api.repository.locale')->findOneByIdentifier('fr_fr');
        $this->get('pimee_security.repository.locale_access')->revokeAccess($locale, [2]);
        $groupIds = $this->getQuery()->getGrantedUserGroupIdsForLocaleCode('fr_FR', Attributes::EDIT_ITEMS);
        Assert::assertCount(1, $groupIds);
        Assert::assertContains('2', $groupIds);


        $groupIds = $this->getQuery()->getGrantedUserGroupIdsForLocaleCode('en_US', Attributes::VIEW_ITEMS);
        Assert::assertCount(3, $groupIds);
        Assert::assertContains('1', $groupIds);
        Assert::assertContains('2', $groupIds);
        Assert::assertContains('3', $groupIds);

        $locale = $this->get('pim_api.repository.locale')->findOneByIdentifier('en_US');
        $this->get('pimee_security.repository.locale_access')->revokeAccess($locale, [1, 2]);
        $groupIds = $this->getQuery()->getGrantedUserGroupIdsForLocaleCode('en_US', Attributes::VIEW_ITEMS);
        Assert::assertCount(2, $groupIds);
        Assert::assertContains('1', $groupIds);
        Assert::assertContains('2', $groupIds);
    }

    protected function getQuery(): GetAccessGroupIdsForLocaleCodeFromDb
    {
        return $this->get('pimee_security.query.get_access_group_id_for_locale_code');
    }
}
