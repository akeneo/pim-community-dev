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

namespace Akeneo\Test\Pim\Automation\SuggestData\Integration\Doctrine\Repository;

use Akeneo\Pim\Automation\SuggestData\Domain\Model\IdentifiersMapping;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Test\Integration\Configuration as TestConfiguration;
use Akeneo\Test\Integration\TestCase;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class IdentifiersMappingRepositoryIntegration extends TestCase
{
    public function test_it_creates_an_identifiers_mapping()
    {
        $mapping = $this->updateMapping(['brand' => $this->getAttribute('sku')]);

        $this->assertEquals([
            [
                'pim_ai_code' => 'brand',
                'attribute_id' => $this->getAttribute('sku')->getId(),
            ],
        ], $mapping);
    }

    public function test_it_updates_an_identifiers_mapping()
    {
        $identifiersMapping = new IdentifiersMapping([
            'brand' => $this->getAttribute('sku'),
        ]);
        $this->get('akeneo.pim.automation.suggest_data.repository.identifiers_mapping')->save($identifiersMapping);

        $this->createAttribute('ean');
        $mapping = $this->updateMapping(['brand' => $this->getAttribute('ean')]);

        $this->assertEquals([
            [
                'pim_ai_code' => 'brand',
                'attribute_id' => $this->getAttribute('ean')->getId(),
            ],
        ], $mapping);
    }

    public function test_it_finds_identifiers_mapping()
    {
        $identifiersMapping = new IdentifiersMapping([
            'brand' => $this->getAttribute('sku'),
        ]);

        $identifiersMappingRepository = $this->get('akeneo.pim.automation.suggest_data.repository.identifiers_mapping');
        $identifiersMappingRepository->save($identifiersMapping);

        $this->get('doctrine.orm.entity_manager')->clear();
        $savedMapping = $identifiersMappingRepository->find();

        $this->assertEquals(
            new IdentifiersMapping([
                'brand' => $this->getAttribute('sku'),
                'mpn' => null,
                'upc' => null,
                'asin' => null,
            ]),
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
     * @param array $newMapping
     *
     * @return array
     */
    private function updateMapping(array $newMapping): array
    {
        $identifiersMapping = new IdentifiersMapping($newMapping);

        $this->get('akeneo.pim.automation.suggest_data.repository.identifiers_mapping')->save($identifiersMapping);

        $entityManager = $this->get('doctrine.orm.entity_manager');
        $entityManager->clear();

        $statement = $entityManager->getConnection()->query(
            'SELECT pim_ai_code, attribute_id from pim_suggest_data_pimai_identifier_mapping;'
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
