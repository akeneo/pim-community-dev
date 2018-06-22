<?php

declare(strict_types=1);

namespace PimEnterprise\Bundle\SuggestDataBundle\tests\integration\Doctrine\Repository;

use Akeneo\Test\Integration\Configuration as TestConfiguration;
use Akeneo\Test\Integration\TestCase;
use PimEnterprise\Component\SuggestData\Model\IdentifiersMapping;

class ConfigurationRepositoryIntegration extends TestCase
{
    /**
     * @test
     */
    public function it_creates_an_identifiers_mapping()
    {
        $this->updateMapping(['brand' => 'sku'], 'sku');
    }

    /**
     * @test
     */
    public function it_updates_an_identifiers_mapping()
    {
        $identifiersMapping = new IdentifiersMapping([
            'brand' => 'sku',
        ]);
        $this->get('pimee_suggest_data.repository.identifiers_mapping')->save($identifiersMapping);

        $this->createAttribute('ean');
        $this->updateMapping(['brand' => 'ean'], 'ean');
    }

    private function updateMapping(array $newMapping, string $newAttributeName)
    {
        $identifiersMapping = new IdentifiersMapping($newMapping);

        $this->get('pimee_suggest_data.repository.identifiers_mapping')->save($identifiersMapping);

        $entityManager = $this->get('doctrine.orm.entity_manager');
        $statement = $entityManager->getConnection()->query(
            'SELECT pim_ai_code, attribute_id from pim_catalog_pimai_identifier_mapping;'
        );
        $identifiersMapping = $statement->fetchAll();

        $this->assertEquals([
            [
                'pim_ai_code' => 'brand',
                'attribute_id' => $this->get('pim_catalog.repository.attribute')->findOneByIdentifier($newAttributeName)->getId(),
            ],
        ], $identifiersMapping);
    }

    /**
     * @test
     */
    public function it_finds_identifiers_mapping()
    {
        $identifiersMapping = new IdentifiersMapping([
            'brand' => 'sku',
        ]);

        $identifiersMappingRepository = $this->get('pimee_suggest_data.repository.identifiers_mapping');
        $identifiersMappingRepository->save($identifiersMapping);

        $savedMapping = $identifiersMappingRepository->findAll();

        $this->assertEquals($identifiersMapping, $savedMapping);
    }

    private function createAttribute(string $code)
    {
        $attribute = $this->getFromTestContainer('akeneo_ee_integration_tests.builder.attribute')->build([
            'code' => $code,
            'type' => 'pim_catalog_text',
            'group' => 'other',
        ]);

        $this->getFromTestContainer('pim_catalog.saver.attribute')->save($attribute);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): TestConfiguration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
