<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Structure\Integration\Attribute\Query;

use Akeneo\Pim\Structure\Component\Query\PublicApi\Attribute\FindBlacklistedAttributesCodesInterface;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;

final class FindBlacklistedAttributeCodesIntegration extends TestCase
{
    private FindBlacklistedAttributesCodesInterface $findBlackListedAttributeCodes;

    public function setUp(): void
    {
        parent::setUp();
        $this->findBlackListedAttributeCodes = $this->get('akeneo.pim.structure.query.find_blacklisted_attribute_codes');
    }

    public function test_it_returns_empty_if_there_is_no_blacklisted_attribute_codes(): void
    {
        $actual = $this->findBlackListedAttributeCodes->all();
        self::assertEmpty($actual);
    }

    public function test_it_returns_all_blacklisted_attribute_codes(): void
    {
        $blacklistedAttributeCodes = ['description', 'image', 'EAN'];
        $this->createBlacklistedAttributeCodes($blacklistedAttributeCodes);

        $actual = $this->findBlackListedAttributeCodes->all();

        self::assertEqualsCanonicalizing($blacklistedAttributeCodes, $actual);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    /**
     * @param array $blacklistedAttributeCodes
     */
    private function createBlacklistedAttributeCodes(array $blacklistedAttributeCodes): void
    {
        $values = implode(
            ',',
            array_map(static fn(string $attributeCode) => sprintf("('%s')", $attributeCode), $blacklistedAttributeCodes)
        );
        $insertBlackListedAttributeCodesQuery = <<<SQL
        INSERT INTO `pim_catalog_attribute_blacklist` (`attribute_code`)
        VALUES $values;
        SQL;

        /** @var Connection $connection */
        $connection = $this->get('database_connection');
        $connection->executeUpdate($insertBlackListedAttributeCodesQuery);
    }
}
