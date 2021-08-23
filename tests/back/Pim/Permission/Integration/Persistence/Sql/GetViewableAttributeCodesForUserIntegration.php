<?php

namespace AkeneoTestEnterprise\Pim\Permission\Integration\Persistence\Sql;

use Akeneo\Pim\Structure\Component\Query\PublicApi\Permission\GetViewableAttributeCodesForUserInterface;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;

class GetViewableAttributeCodesForUserIntegration extends TestCase
{
    /**
     * @test
     */
    public function it_filters_a_list_of_attribute_codes_by_user_permission()
    {
        $query = $this->getQuery();

        $userId = $this->get('database_connection')
            ->fetchColumn('SELECT id FROM oro_user WHERE username = "mary"', [], 0);

        $filteredAttributes = $query->forAttributeCodes(['a_date', 'a_multi_select'], $userId);

        Assert::assertSame($filteredAttributes, ['a_date']);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }

    private function getQuery(): GetViewableAttributeCodesForUserInterface
    {
        return $this->get('pimee_security.query.get_viewable_attribute_codes_for_user');
    }
}
