<?php

namespace Pim\Bundle\CatalogBundle\tests\fixture;

use Pim\Component\Catalog\Model\Product;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Helper to create some catalog entities, such as VariantProduct and ProductModel
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class EntityBuilder
{
    /** @var ContainerInterface */
    private $container;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param string $identifier
     * @param string $familyCode
     * @param array  $data
     *
     * @return ProductInterface
     */
    public function createProduct(
        string $identifier,
        string $familyCode,
        array $data
    ): ProductInterface {
        $product = $this->container->get('pim_catalog.builder.product')->createProduct($identifier, $familyCode);
        $this->container->get('pim_catalog.updater.product')->update($product, $data);
        $this->container->get('validator')->validate($product);
        $this->container->get('pim_catalog.saver.product')->save($product);

        return $product;
    }

    /**
     * @param array $data
     *
     * @return mixed
     */
    public function createFamilyVariant(array $data)
    {
        $family = $this->container->get('pim_catalog.factory.family_variant')->create();
        $this->container->get('pim_catalog.updater.family_variant')->update($family, $data);
        $this->container->get('validator')->validate($family);
        $this->container->get('pim_catalog.saver.family_variant')->save($family);

        return $family;
    }

    /**
     * @param string                     $identifier
     * @param string                     $familyVariantCode
     * @param null|ProductModelInterface $parent
     * @param array                      $data
     *
     * @return ProductModelInterface
     */
    public function createProductModel(
        string $identifier,
        string $familyVariantCode,
        ?ProductModelInterface $parent,
        array $data
    ): ProductModelInterface {
        $productModel = $this->container->get('pim_catalog.factory.product_model')->create();

        $this->container->get('pim_catalog.updater.product_model')->update($productModel, [
            'code'           => $identifier,
            'family_variant' => $familyVariantCode,
        ]);

        if (null !== $parent) {
            $productModel->setParent($parent);
        }

        $this->updateProductModel($productModel, $data);

        return $productModel;
    }

    /**
     * TODO: use the factory/builder of variant products when it exists
     *
     * Creates a variant product with identifier and product model parent
     *
     * @param string                $identifier
     * @param string                $familyCode
     * @param string                $familyVariantCode
     * @param ProductModelInterface $parent
     * @param array                 $data
     *
     * @return ProductInterface
     */
    public function createVariantProduct(
        string $identifier,
        string $familyCode,
        string $familyVariantCode,
        ProductModelInterface $parent,
        array $data
    ): ProductInterface {
        $variantProduct = new Product();

        $identifierAttribute = $this->container->get('pim_catalog.repository.attribute')->findOneByCode('sku');

        $entityWithValuesBuilder = $this->container->get('pim_catalog.builder.entity_with_values');
        $entityWithValuesBuilder->addOrReplaceValue($variantProduct, $identifierAttribute, null, null, $identifier);

        $this->container->get('pim_catalog.updater.product')->update(
            $variantProduct,
            [
                'family' => $familyCode,
            ]
        );

        $variantProduct->setParent($parent);

        $familyVariant = $this->container->get('pim_catalog.repository.family_variant')->findOneByCode($familyVariantCode);
        $variantProduct->setFamilyVariant($familyVariant);

        $this->updateVariantProduct($variantProduct, $data);

        return $variantProduct;
    }

    /**
     * @param ProductModelInterface $productModel
     * @param array                 $data
     */
    protected function updateProductModel(ProductModelInterface $productModel, array $data): void
    {
        $this->container->get('pim_catalog.updater.product_model')->update($productModel, $data);
        $this->container->get('pim_catalog.saver.product_model')->save($productModel);

        $launcher = $this->container->get('akeneo_integration_tests.launcher.job_launcher');

        while ($launcher->hasJobInQueue()) {
            $launcher->launchConsumerOnce();
        }

        $this->container->get('akeneo_elasticsearch.client.product_and_product_model')->refreshIndex();
    }

    /**
     * @param ProductInterface $variantProduct
     * @param array            $data
     */
    protected function updateVariantProduct(ProductInterface $variantProduct, array $data): void
    {
        $this->container->get('pim_catalog.updater.product')->update($variantProduct, $data);
        $this->container->get('pim_catalog.saver.product')->save($variantProduct);
        $this->container->get('akeneo_elasticsearch.client.product_and_product_model')->refreshIndex();
    }
}
