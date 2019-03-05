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
use Akeneo\Pim\Automation\FranklinInsights\Domain\FamilyAttribute\Model\Read\Attribute;
use Akeneo\Pim\Automation\FranklinInsights\Domain\IdentifierMapping\Model\IdentifiersMapping;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
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
                'asin' => $this->buildAttribute('sku'),
                'upc' => $this->buildAttribute('test'),
            ]
        );

        $this->getFromTestContainer('akeneo.pim.automation.franklin_insights.repository.identifiers_mapping')
             ->save($identifiersMapping);

        $this->assertMappingEquals(
            [
                [
                    'franklin_code' => 'brand',
                    'attribute_id' => null,
                ],
                [
                    'franklin_code' => 'mpn',
                    'attribute_id' => null,
                ],
                [
                    'franklin_code' => 'upc',
                    'attribute_id' => $this->getAttribute('test')->getCode(),
                ],
                [
                    'franklin_code' => 'asin',
                    'attribute_id' => $this->getAttribute('sku')->getCode(),
                ],
            ]
        );
    }

    public function test_that_it_can_update_an_existing_identifiers_mapping(): void
    {
        $this->insertIdentifiersMapping(['asin' => 'sku']);

        $this->createAttribute('asin');
        $identifiersMapping = $this
            ->getFromTestContainer('akeneo.pim.automation.franklin_insights.repository.identifiers_mapping')
            ->find();

        $asin = $this->buildAttribute('asin');
        $sku = $this->buildAttribute('sku');

        $identifiersMapping
            ->map('asin', $asin)
            ->map('upc', $sku);

        $this->getFromTestContainer('akeneo.pim.automation.franklin_insights.repository.identifiers_mapping')
             ->save($identifiersMapping);

        $this->assertMappingEquals(
            [
                [
                    'franklin_code' => 'brand',
                    'attribute_id' => null,
                ],
                [
                    'franklin_code' => 'mpn',
                    'attribute_id' => null,
                ],
                [
                    'franklin_code' => 'upc',
                    'attribute_id' => $sku->getCode(),
                ],
                [
                    'franklin_code' => 'asin',
                    'attribute_id' => $asin->getCode(),
                ],
            ]
        );
    }

    public function test_that_it_finds_an_identifiers_mapping(): void
    {
        $this->insertIdentifiersMapping(['asin' => 'sku']);
        $this->getFromTestContainer('doctrine.orm.entity_manager')->clear();

        $savedMapping = $this
            ->getFromTestContainer('akeneo.pim.automation.franklin_insights.repository.identifiers_mapping')
            ->find();

        $this->assertEquals(
            new IdentifiersMapping(['asin' => $this->buildAttribute('sku')]),
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
INSERT INTO pimee_franklin_insights_identifier_mapping(franklin_code, attribute_id)
VALUES (:franklinCode, :attributeId);
SQL;

        $connection = $this->getFromTestContainer('database_connection');
        foreach (IdentifiersMapping::FRANKLIN_IDENTIFIERS as $identifierCode) {
            $attribute = $this->getAttribute($mappedAttributes[$identifierCode] ?? null);
            $connection->executeQuery(
                $insertQuery,
                [
                    'franklinCode' => $identifierCode,
                    'attributeId' => $attribute ? $attribute->getCode() : null,
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
        return $this->getFromTestContainer('database_connection')
                    ->query('SELECT franklin_code, attribute_id from pimee_franklin_insights_identifier_mapping;')
                    ->fetchAll();
    }

    /**
     * @param string $code
     */
    private function createAttribute(string $code): void
    {
        $attribute = $this->getFromTestContainer('akeneo_ee_integration_tests.builder.attribute')->build(
            [
                'code' => $code,
                'type' => 'pim_catalog_text',
                'group' => 'other',
            ]
        );

        $this->getFromTestContainer('pim_catalog.saver.attribute')->save($attribute);
    }

    /**
     * @param string|null $name
     *
     * @return AttributeInterface|null
     */
    private function getAttribute(?string $name): ?AttributeInterface
    {
        return null !== $name ?
            $this->getFromTestContainer('pim_catalog.repository.attribute')->findOneByIdentifier($name) :
            null;
    }

    private function buildAttribute($name)
    {
        return new Attribute(new AttributeCode($name), $this->getAttribute($name)->getId(), 'pim_catalog_identifier', false, false, false, false, ['en_US' => 'SKU'], null, null);
    }
}
