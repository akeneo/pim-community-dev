<?php

namespace AkeneoTestEnterprise\Pim\Permission\Integration\Persistence\Sql;

use Akeneo\Pim\Permission\Component\Query\GetViewableCategoryCodesForUserInterface;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;

class GetViewableCategoryCodesForUserIntegration extends TestCase
{
    /**
     * @test
    */
    public function it_filters_a_list_of_category_codes_by_user_permission()
    {
        $query = $this->getQuery();

        $userId = $this
            ->get('database_connection')
            ->fetchColumn('SELECT id FROM oro_user WHERE username = "mary"', [], 0);

        $filteredCategoryCodes = $query->forCategoryCodes(['categoryC', 'categoryB', 'categoryA1', 'master'], $userId);

        Assert::assertEqualsCanonicalizing(['categoryA1', 'master'], $filteredCategoryCodes);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }

    private function getQuery(): GetViewableCategoryCodesForUserInterface
    {
        return $this->get('pimee_security.query.sql.get_viewable_category_codes_for_user');
    }
}
