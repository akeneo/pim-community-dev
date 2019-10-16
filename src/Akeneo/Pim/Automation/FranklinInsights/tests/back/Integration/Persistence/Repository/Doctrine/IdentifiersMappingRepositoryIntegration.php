<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Test\Pim\Automation\FranklinInsights\Integration\Persistence\Repository\Doctrine;

use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\AttributeCode;
use Akeneo\Pim\Automation\FranklinInsights\Domain\IdentifierMapping\Model\IdentifiersMapping;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class IdentifiersMappingRepositoryIntegration extends TestCase
{
    public function test_that_it_creates_an_identifiers_mapping(): void
    {
        $this->createAttribute('test');
        $identifiersMapping = new IdentifiersMapping(
            [
                'asin' => 'sku',
                'upc' => 'test',
            ]
        );

        $this->get('akeneo.pim.automation.franklin_insights.repository.identifiers_mapping')
             ->save($identifiersMapping);

        $this->assertMappingEquals(
            [
                [
                    'franklin_code' => 'brand',
                    'attribute_code' => null,
                ],
                [
                    'franklin_code' => 'mpn',
                    'attribute_code' => null,
                ],
                [
                    'franklin_code' => 'upc',
                    'attribute_code' => new AttributeCode('test'),
                ],
                [
                    'franklin_code' => 'asin',
                    'attribute_code' => new AttributeCode('sku'),
                ],
            ]
        );
    }

    public function test_that_it_can_update_an_existing_identifiers_mapping(): void
    {
        $this->insertIdentifiersMapping(['asin' => 'sku']);

        $this->createAttribute('asin');
        $identifiersMapping = $this
            ->get('akeneo.pim.automation.franklin_insights.repository.identifiers_mapping')
            ->find();

        $identifiersMapping
            ->map('asin', new AttributeCode('asin'))
            ->map('upc', new AttributeCode('sku'));

        $this->get('akeneo.pim.automation.franklin_insights.repository.identifiers_mapping')
             ->save($identifiersMapping);

        $this->assertMappingEquals(
            [
                [
                    'franklin_code' => 'brand',
                    'attribute_code' => null,
                ],
                [
                    'franklin_code' => 'mpn',
                    'attribute_code' => null,
                ],
                [
                    'franklin_code' => 'upc',
                    'attribute_code' => new AttributeCode('sku'),
                ],
                [
                    'franklin_code' => 'asin',
                    'attribute_code' => new AttributeCode('asin'),
                ],
            ]
        );
    }

    public function test_that_it_finds_an_identifiers_mapping(): void
    {
        $this->insertIdentifiersMapping(['asin' => 'sku']);
        $this->get('doctrine.orm.entity_manager')->clear();

        $savedMapping = $this
            ->get('akeneo.pim.automation.franklin_insights.repository.identifiers_mapping')
            ->find();

        $this->assertEquals(
            new IdentifiersMapping(['asin' => 'sku']),
            $savedMapping
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    /**
     * @param array $mappedAttributes
     */
    private function insertIdentifiersMapping(array $mappedAttributes): void
    {
        $insertQuery = <<<SQL
INSERT INTO pimee_franklin_insights_identifier_mapping(franklin_code, attribute_code)
VALUES (:franklinCode, :attributeCode);
SQL;

        $connection = $this->get('database_connection');
        foreach (IdentifiersMapping::FRANKLIN_IDENTIFIERS as $identifierCode) {
            $attributeCode = $mappedAttributes[$identifierCode] ?? null;
            $connection->executeQuery(
                $insertQuery,
                [
                    'franklinCode' => $identifierCode,
                    'attributeCode' => $attributeCode,
                ]
            );
        }
    }

    private function assertMappingEquals(array $expectedMapping): void
    {
        $databaseContent = $this->getIdentifiersMapping();

        $this->assertEquals($expectedMapping, $databaseContent);
    }

    /**
     * @return array
     */
    private function getIdentifiersMapping(): array
    {
        return $this->get('database_connection')
                    ->query('SELECT franklin_code, attribute_code from pimee_franklin_insights_identifier_mapping;')
                    ->fetchAll();
    }

    /**
     * @param string $code
     */
    private function createAttribute(string $code): void
    {
        $attribute = $this->get('akeneo_ee_integration_tests.builder.attribute')->build(
            [
                'code' => $code,
                'type' => 'pim_catalog_text',
                'group' => 'other',
            ]
        );

        $this->get('pim_catalog.saver.attribute')->save($attribute);
    }
}
