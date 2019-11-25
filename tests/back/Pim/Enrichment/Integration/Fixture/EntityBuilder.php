<?php

namespace AkeneoTest\Pim\Enrichment\Integration\Fixture;

use Akeneo\Pim\Enrichment\Component\Product\Builder\EntityWithValuesBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Builder\ProductBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Pim\Structure\Component\Repository\FamilyVariantRepositoryInterface;
use Akeneo\Test\IntegrationTestsBundle\Launcher\JobLauncher;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Helper to create some catalog entities, such as VariantProduct and ProductModel
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class EntityBuilder
{
    /** @var JobLauncher */
    private $jobLauncher;

    /** @var ValidatorInterface */
    private $validator;

    /** @var EntityWithValuesBuilderInterface */
    private $entityWithValuesBuilder;

    /** @var ProductBuilderInterface */
    private $productBuilder;

    /** @var ObjectUpdaterInterface */
    private $productUpdater;

    /** @var SaverInterface */
    private $productSaver;

    /** @var SimpleFactoryInterface */
    private $productModelFactory;

    /** @var ObjectUpdaterInterface */
    private $productModelUpdater;

    /** @var SaverInterface */
    private $productModelSaver;

    /** @var FamilyVariantRepositoryInterface */
    private $familyVariantRepository;

    /** @var SimpleFactoryInterface */
    private $familyVariantFactory;

    /** @var ObjectUpdaterInterface */
    private $familyVariantUpdater;

    /** @var SaverInterface */
    private $familyVariantSaver;

    /** @var AttributeRepositoryInterface */
    private $attributeRepository;

    /** @var Client */
    private $esClient;

    public function __construct(
        JobLauncher $jobLauncher,
        ValidatorInterface $validator,
        EntityWithValuesBuilderInterface $entityWithValuesBuilder,
        ProductBuilderInterface $productBuilder,
        ObjectUpdaterInterface $productUpdater,
        SaverInterface $productSaver,
        SimpleFactoryInterface $productModelFactory,
        ObjectUpdaterInterface $productModelUpdater,
        SaverInterface $productModelSaver,
        FamilyVariantRepositoryInterface $familyVariantRepository,
        SimpleFactoryInterface $familyVariantFactory,
        ObjectUpdaterInterface $familyVariantUpdater,
        SaverInterface $familyVariantSaver,
        AttributeRepositoryInterface $attributeRepository,
        Client $esClient
    ) {
        $this->jobLauncher = $jobLauncher;
        $this->validator = $validator;
        $this->entityWithValuesBuilder = $entityWithValuesBuilder;
        $this->productBuilder = $productBuilder;
        $this->productUpdater = $productUpdater;
        $this->productSaver = $productSaver;
        $this->productModelFactory = $productModelFactory;
        $this->productModelUpdater = $productModelUpdater;
        $this->productModelSaver = $productModelSaver;
        $this->familyVariantRepository = $familyVariantRepository;
        $this->familyVariantFactory = $familyVariantFactory;
        $this->familyVariantUpdater = $familyVariantUpdater;
        $this->familyVariantSaver = $familyVariantSaver;
        $this->attributeRepository = $attributeRepository;
        $this->esClient = $esClient;
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
        $product = $this->productBuilder->createProduct($identifier, $familyCode);
        $this->productUpdater->update($product, $data);
        $this->validator->validate($product);
        $this->productSaver->save($product);

        return $product;
    }

    /**
     * @param array $data
     *
     * @return mixed
     */
    public function createFamilyVariant(array $data)
    {
        $family = $this->familyVariantFactory->create();
        $this->familyVariantUpdater->update($family, $data);
        $this->validator->validate($family);
        $this->familyVariantSaver->save($family);

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
        $productModel = $this->productModelFactory->create();

        $this->productModelUpdater->update(
            $productModel,
            [
                'code'           => $identifier,
                'family_variant' => $familyVariantCode,
            ]
        );

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

        $identifierAttribute = $this->attributeRepository->findOneByCode('sku');

        $this->entityWithValuesBuilder->addOrReplaceValue($variantProduct, $identifierAttribute, null, null, $identifier);

        $this->productUpdater->update(
            $variantProduct,
            [
                'family' => $familyCode,
            ]
        );

        $variantProduct->setParent($parent);

        $familyVariant = $this->familyVariantRepository->findOneByCode(
            $familyVariantCode
        );
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
        $this->productModelUpdater->update($productModel, $data);
        $this->productModelSaver->save($productModel);

        $this->esClient->refreshIndex();
    }

    /**
     * @param ProductInterface $variantProduct
     * @param array            $data
     */
    protected function updateVariantProduct(ProductInterface $variantProduct, array $data): void
    {
        $this->productUpdater->update($variantProduct, $data);
        $this->productSaver->save($variantProduct);
        $this->esClient->refreshIndex();
    }
}
