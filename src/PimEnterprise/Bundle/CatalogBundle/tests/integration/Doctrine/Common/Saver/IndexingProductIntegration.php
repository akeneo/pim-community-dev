<?php

namespace PimEnterprise\Bundle\CatalogBundle\tests\integration\Doctrine\Common\Saver;

use Akeneo\Bundle\ElasticsearchBundle\Client;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

/**
 * Test the full products have been correctly indexed after being saved and not only the granted data
 * They should be indexed in 2 indexes:
 *      - pim_catalog_product
 *      - pim_catalog_product_and_product_model
 */
class IndexingProductIntegration extends TestCase
{
    private const DOCUMENT_TYPE = 'pim_catalog_product';

    /** @var Client */
    private $esProductClient;

    /** @var Client */
    private $esProductAndProductModelClient;

    /** @var ObjectUpdaterInterface */
    private $productUpdater;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $this->esProductClient = $this->get('akeneo_elasticsearch.client.product');
        $this->esProductAndProductModelClient = $this->get('akeneo_elasticsearch.client.product_and_product_model');
        $this->productUpdater = $this->get('pim_catalog.updater.product');

        $bar = $this->get('pim_catalog.builder.product')->createProduct('bar');
        $this->productUpdater->update($bar, []);

        $baz = $this->get('pim_catalog.builder.product')->createProduct('baz');
        $this->productUpdater->update($baz, [
            'categories' => ['categoryB'],
        ]);
        $this->get('pim_catalog.saver.product')->saveAll([$bar, $baz]);

        $foo = $this->get('pim_catalog.builder.product')->createProduct('foo');
        $this->productUpdater->update($foo, [
            'categories' => ['master', 'categoryA', 'categoryA1', 'categoryB'],
            'values' => [
                'a_text' => [['data' => 'foo', 'locale' => null, 'scope' => null]],
                'a_number_float' => [['data' => '15.6', 'locale' => null, 'scope' => null]],
                'a_multi_select' => [['data' => ['optionA'], 'locale' => null, 'scope' => null]],
            ],
            'associations' => [
                'X_SELL' => [
                    'products' => ['bar', 'baz']
                ]
            ]
        ]);

        $this->get('pim_catalog.saver.product')->save($foo);
    }

    public function testIndexingProductsOnBulkSave()
    {
        $user = $this->get('pim_user.repository.user')->findOneByIdentifier('mary');
        $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
        $this->get('security.token_storage')->setToken($token);

        $foo = $this->get('pim_catalog.repository.product')->findOneByIdentifier('foo');

        $indexedProductFoo = $this->esProductClient->get(self::DOCUMENT_TYPE, $foo->getId());
        $this->assertTrue($indexedProductFoo['found']);

        $this->productUpdater->update($foo, [
            'values' => [
                'a_text' => [['data' => 'my data updated', 'locale' => null, 'scope' => null]],
            ],
        ]);
        $this->get('pim_catalog.saver.product')->saveAll([$foo]);
        $indexedProductFoo = $this->esProductClient->get(self::DOCUMENT_TYPE, $foo->getId());
        $this->assertSame(['categoryA', 'categoryA1', 'categoryB', 'master'], $indexedProductFoo['_source']['categories']);
        $this->assertSame(['<all_channels>' => ['<all_locales>' => 'my data updated']], $indexedProductFoo['_source']['values']['a_text-text']);
        $this->assertSame(['<all_channels>' => ['<all_locales>' => '15.6000']], $indexedProductFoo['_source']['values']['a_number_float-decimal']);
        $this->assertSame(['<all_channels>' => ['<all_locales>' => ['optionA']]], $indexedProductFoo['_source']['values']['a_multi_select-options']);
    }

    public function testIndexingProductOnUnitarySave()
    {
        $user = $this->get('pim_user.repository.user')->findOneByIdentifier('mary');
        $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
        $this->get('security.token_storage')->setToken($token);

        $foo = $this->get('pim_catalog.repository.product')->findOneByIdentifier('foo');

        $indexedProductFoo = $this->esProductClient->get(self::DOCUMENT_TYPE, $foo->getId());
        $this->assertTrue($indexedProductFoo['found']);

        $this->productUpdater->update($foo, [
            'values' => [
                'a_text' => [['data' => 'my data updated', 'locale' => null, 'scope' => null]],
            ],
        ]);
        $this->get('pim_catalog.saver.product')->save($foo);
        $indexedProductFoo = $this->esProductClient->get(self::DOCUMENT_TYPE, $foo->getId());
        $this->assertSame(['categoryA', 'categoryA1', 'categoryB', 'master'], $indexedProductFoo['_source']['categories']);
        $this->assertSame(['<all_channels>' => ['<all_locales>' => 'my data updated']], $indexedProductFoo['_source']['values']['a_text-text']);
        $this->assertSame(['<all_channels>' => ['<all_locales>' => '15.6000']], $indexedProductFoo['_source']['values']['a_number_float-decimal']);
        $this->assertSame(['<all_channels>' => ['<all_locales>' => ['optionA']]], $indexedProductFoo['_source']['values']['a_multi_select-options']);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        $rootPath = $this->getParameter('kernel.root_dir') . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR;
        return new Configuration(
            [
                Configuration::getTechnicalCatalogPath(),
                $rootPath . 'tests' . DIRECTORY_SEPARATOR . 'catalog' . DIRECTORY_SEPARATOR . 'technical'
            ]
        );
    }
}
