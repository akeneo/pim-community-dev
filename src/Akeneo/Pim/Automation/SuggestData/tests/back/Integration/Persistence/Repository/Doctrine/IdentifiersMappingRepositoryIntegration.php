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

namespace Akeneo\Test\Pim\Automation\SuggestData\Integration\Persistence\Repository\Doctrine;

use Akeneo\Pim\Automation\SuggestData\Domain\IdentifierMapping\Model\IdentifiersMapping;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Test\Integration\Configuration as TestConfiguration;
use Akeneo\Test\Integration\TestCase;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class IdentifiersMappingRepositoryIntegration extends TestCase
{
    public function test_it_creates_an_identifiers_mapping(): void
    {
        $identifiersMapping = $this->get('akeneo.pim.automation.suggest_data.repository.identifiers_mapping')->find();

        $this->createAttribute('test');
        $identifiersMapping->map('asin', $this->getAttribute('sku'));
        $identifiersMapping->map('upc', $this->getAttribute('test'));
        $this->saveIdentifiersMapping($identifiersMapping);

        $this->assertMappingEquals([
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
                'attribute_id' => $this->getAttribute('test')->getId(),
            ],
            [
                'franklin_code' => 'asin',
                'attribute_id' => $this->getAttribute('sku')->getId(),
            ],
        ]);
    }

    public function test_it_updates_an_identifiers_mapping(): void
    {
        $identifiersMapping = new IdentifiersMapping();
        $identifiersMapping->map('asin', $this->getAttribute('sku'));
        $this->get('akeneo.pim.automation.suggest_data.repository.identifiers_mapping')->save($identifiersMapping);

        $this->createAttribute('ean');
        $identifiersMapping = $this->get('akeneo.pim.automation.suggest_data.repository.identifiers_mapping')->find();
        $identifiersMapping->map('upc', $this->getAttribute('ean'));
        $this->saveIdentifiersMapping($identifiersMapping);

        $this->assertMappingEquals([
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
                'attribute_id' => $this->getAttribute('ean')->getId(),
            ],
            [
                'franklin_code' => 'asin',
                'attribute_id' => $this->getAttribute('sku')->getId(),
            ],
        ]);
    }

    public function test_it_finds_identifiers_mapping(): void
    {
        $identifiersMappingRepository = $this->get('akeneo.pim.automation.suggest_data.repository.identifiers_mapping');

        $identifiersMapping = new IdentifiersMapping();
        $identifiersMapping->map('asin', $this->getAttribute('sku'));
        $identifiersMappingRepository->save($identifiersMapping);

        $this->get('doctrine.orm.entity_manager')->clear();
        $savedMapping = $identifiersMappingRepository->find();

        $this->assertEquals(
            (new IdentifiersMapping())->map('asin', $this->getAttribute('sku')),
            $savedMapping
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): TestConfiguration
    {
        return $this->catalog->useMinimalCatalog();
    }

    /**
     * @param IdentifiersMapping $identifiersMapping
     */
    private function saveIdentifiersMapping(IdentifiersMapping $identifiersMapping): void
    {
        $this->get('akeneo.pim.automation.suggest_data.repository.identifiers_mapping')->save($identifiersMapping);

        $entityManager = $this->get('doctrine.orm.entity_manager');
        $entityManager->clear();
    }

    private function assertMappingEquals(array $expectedMapping): void
    {
        $databaseContent = $this->getIdentifiersMapping();

        $this->assertEquals($expectedMapping, $databaseContent);
    }

    private function getIdentifiersMapping()
    {
        $entityManager = $this->get('doctrine.orm.entity_manager');
        $statement = $entityManager->getConnection()->query(
            'SELECT franklin_code, attribute_id from pim_suggest_data_franklin_identifier_mapping;'
        );

        return $statement->fetchAll();
    }

    /**
     * @param string $code
     */
    private function createAttribute(string $code): void
    {
        $attribute = $this->getFromTestContainer('akeneo_ee_integration_tests.builder.attribute')->build([
            'code' => $code,
            'type' => 'pim_catalog_text',
            'group' => 'other',
        ]);

        $this->getFromTestContainer('pim_catalog.saver.attribute')->save($attribute);
    }

    /**
     * @param string $name
     *
     * @return AttributeInterface
     */
    private function getAttribute(string $name): AttributeInterface
    {
        return $this->get('pim_catalog.repository.attribute')->findOneByIdentifier($name);
    }
}
