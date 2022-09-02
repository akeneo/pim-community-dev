<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Structure\Integration\Family;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetFamily;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetTextValue;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use AkeneoTest\Pim\Enrichment\Integration\PQB\AbstractProductQueryBuilderTestCase;
use Webmozart\Assert\Assert;

class FamilyAttributeAsLabelChangedSubscriberIntegration extends AbstractProductQueryBuilderTestCase
{
    private Client $esClient;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createAdminUser();

        $this->esClient = $this->get('akeneo_elasticsearch.client.product_and_product_model');
    }

    public function test_it_reindex_the_product_label_when_the_attribute_as_label_change_from_a_family()
    {
        $familyCode = 'my_family1';

        $this->givenAttributes([
            'name', 'meta_title'
        ]);

        $this->givenFamilies([
            [
                'code' => $familyCode,
                'attributes' => [
                    'sku', 'name', 'meta_title'
                ],
                'attribute_as_label' => 'name'
            ]
        ]);

        $this->createProduct('my_product1', [
            new SetFamily($familyCode),
            new SetTextValue('name', null, null, 'ABCD'),
            new SetTextValue('meta_title', null, null, 'DCBA'),
        ]);

        $family = $this->get('pim_catalog.repository.family')->findOneByCode($familyCode);
        $attribute = $this->get('pim_catalog.repository.attribute')->findOneByCode('meta_title');
        $this->updateFamilyAttributeAsLabel($family, $attribute);

        $this->esClient->refreshIndex();

        $productIndex = $this->esClient->search([
            '_source' => ['label'],
            'query' => [
                'match' => [
                    'identifier' => 'my_product1'
                ]
            ]
        ]);

        $labelIndex = $productIndex['hits']['hits'][0]['_source']['label']['<all_channels>']['<all_locales>'];

        $this->assertEquals('DCBA', $labelIndex);
    }

    /**
     * @return Configuration
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function givenAttributes(array $attributeCodes): void
    {
        $attributes = array_map(function ($attributeCode) {
            $attribute = $this->get('pim_catalog.factory.attribute')->create();
            $this->get('pim_catalog.updater.attribute')->update(
                $attribute,
                [
                    'code' => $attributeCode,
                    'type' => 'pim_catalog_text',
                    'group' => 'other'
                ]
            );

            $errors = $this->get('validator')->validate($attribute);
            Assert::count($errors, 0);

            return $attribute;
        }, $attributeCodes);

        $this->get('pim_catalog.saver.attribute')->saveAll($attributes);
    }

    private function givenFamilies(array $families): void
    {
        $families = array_map(function (array $familyData) {
            $family = $this->get('pim_catalog.factory.family')->create();
            $this->get('pim_catalog.updater.family')->update($family, $familyData);
            $constraintViolations = $this->get('validator')->validate($family);

            Assert::count($constraintViolations, 0);

            return $family;
        }, $families);

        $this->get('pim_catalog.saver.family')->saveAll($families);
    }

    private function updateFamilyAttributeAsLabel(FamilyInterface $family, AttributeInterface $attribute): void
    {
        $family->setAttributeAsLabel($attribute);

        $errors = $this->get('validator')->validate($family);
        static::assertCount(0, $errors);
        $this->get('pim_catalog.saver.family')->save($family);
    }
}
