<?php

declare(strict_types=1);

namespace AkeneoTest\Platform\Integration\Analytics\Query;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Component\Analytics\MediaCountQuery;
use PHPUnit\Framework\Assert;

class MediaCountIntegration extends TestCase
{
    private MediaCountQuery $mediaCount;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mediaCount = $this->get('pim_analytics.query.media_count');

        $this->createProduct('product_1', [
            'values'     => [
                'a_localizable_scopable_image' => [
                    ['data' => $this->getFileInfoKey($this->getFixturePath('akeneo.jpg')), 'locale' => 'en_US', 'scope' => 'ecommerce'],
                    ['data' => $this->getFileInfoKey($this->getFixturePath('akeneo.jpg')), 'locale' => 'fr_FR', 'scope' => 'tablet'],
                ],
                'a_file' => [
                    ['data' => $this->getFileInfoKey($this->getFixturePath('akeneo.pdf')), 'locale' => null, 'scope' => null],
                ]
            ]
        ]);

        $this->createProduct('product_2', [
            'values'     => [
                'a_localizable_image' => [
                    ['data' => $this->getFileInfoKey($this->getFixturePath('akeneo.jpg')), 'locale' => 'en_US', 'scope' => null],
                    ['data' => $this->getFileInfoKey($this->getFixturePath('akeneo.jpg')), 'locale' => 'fr_FR', 'scope' => null],
                ],
                'a_file' => [
                    ['data' => $this->getFileInfoKey($this->getFixturePath('akeneo.pdf')), 'locale' => null, 'scope' => null],
                ]
            ]
        ]);

        $this->createProduct('product_3', [
            'values'     => [
                'a_scopable_image' => [
                    ['data' => $this->getFileInfoKey($this->getFixturePath('akeneo.jpg')), 'locale' => null, 'scope' => 'tablet'],
                    ['data' => $this->getFileInfoKey($this->getFixturePath('akeneo.jpg')), 'locale' => null, 'scope' => 'ecommerce'],
                ]
            ]
        ]);
    }

    public function test_it_fetches_the_number_of_media_files()
    {
        $result = $this->mediaCount->countFiles();

        Assert::assertEquals(2, $result);
    }

    public function test_it_fetches_the_number_of_media_images()
    {
        $result = $this->mediaCount->countImages();

        Assert::assertEquals(6, $result);
    }

    /**
     * @param string $identifier
     * @param array  $data
     *
     * @return ProductInterface
     * @throws \Exception
     */
    protected function createProduct($identifier, array $data = [])
    {
        $product = $this->get('pim_catalog.builder.product')->createProduct($identifier);
        $this->get('pim_catalog.updater.product')->update($product, $data);

        $errors = $this->get('pim_catalog.validator.product')->validate($product);
        if (0 !== $errors->count()) {
            throw new \Exception(sprintf(
                'Impossible to setup test in %s: %s',
                static::class,
                $errors->get(0)->getMessage()
            ));
        }

        $this->get('pim_catalog.saver.product')->save($product);
        $this->get('akeneo_elasticsearch.client.product_and_product_model')->refreshIndex();


        return $product;
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
