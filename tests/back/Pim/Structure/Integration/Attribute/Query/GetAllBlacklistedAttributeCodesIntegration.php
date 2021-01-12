<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Structure\Integration\Family;

use Akeneo\Pim\Structure\Bundle\Manager\AttributeCodeBlacklister;
use Akeneo\Pim\Structure\Bundle\Query\InternalApi\Attribute\GetAllBlacklistedAttributeCodes;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;

final class GetAllBlacklistedAttributeCodesIntegration extends TestCase
{
    private Connection $sqlConnection;

    public function setUp(): void
    {
        parent::setUp();

        $this->sqlConnection = $this->get('database_connection');
    }

    public function test_it_returns_an_array_of_blacklisted_attributes(): void
    {
        $blacklister = $this->getBlacklister();
        $blacklister->blacklist('description');
        $blacklister->blacklist('name');

        $query = $this->getQuery();
        $result = $query->execute();

        $this->assertEquals($result, ['description', 'name']);
    }

    public function test_it_returns_an_empty_array_if_no_attributes_are_blacklisted(): void
    {
        $query = $this->getQuery();
        $result = $query->execute();

        $this->assertEquals($result, []);
    }

    private function getQuery(): GetAllBlacklistedAttributeCodes
    {
        return $this->get('akeneo.pim.structure.query.get_all_blacklisted_attribute_codes');
    }

    private function getBlacklister(): AttributeCodeBlacklister
    {
        return $this->get('pim_catalog.manager.attribute_code_blacklister');
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
