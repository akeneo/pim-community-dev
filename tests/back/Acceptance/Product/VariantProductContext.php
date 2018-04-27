<?php

declare(strict_types=1);

namespace Akeneo\Test\Acceptance\Product;

use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Akeneo\Test\Acceptance\ProductModel\InMemoryProductModelRepository;
use Akeneo\Test\Common\Builder\EntityWithValue\ProductBuilder;
use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use Pim\Component\Catalog\Model\ProductInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Webmozart\Assert\Assert;

/**
 * @author    Damien Carcel <damien.carcel@akeneo.com>
 * @author    Julian Prud'homme <julian.prudhomme@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class VariantProductContext implements Context
{
    /** @var InMemoryProductModelRepository */
    private $productModelRepository;

    /** @var InMemoryProductRepository */
    private $productRepository;

    /** @var ObjectUpdaterInterface */
    private $productUpdater;

    /** @var ValidatorInterface */
    private $validator;

    /** @var ProductBuilder */
    private $productBuilder;

    /** @var \Exception */
    private $exception;

    /**
     * @param InMemoryProductModelRepository $productModelRepository
     * @param InMemoryProductRepository      $productRepository
     * @param ObjectUpdaterInterface         $productUpdater
     * @param ValidatorInterface             $validator
     * @param ProductBuilder                 $productBuilder
     */
    public function __construct(
        InMemoryProductModelRepository $productModelRepository,
        InMemoryProductRepository $productRepository,
        ObjectUpdaterInterface $productUpdater,
        ValidatorInterface $validator,
        ProductBuilder $productBuilder
    ) {
        $this->productModelRepository = $productModelRepository;
        $this->productRepository = $productRepository;
        $this->productUpdater = $productUpdater;
        $this->validator = $validator;
        $this->productBuilder = $productBuilder;
    }

    /**
     * @param string $identifier
     * @param string $parentCode
     *
     * @Given a variant product :identifier with :parentCode as parent with the following axis values:
     */
    public function createVariantProduct(string $identifier, string $parentCode, TableNode $axisValues)
    {
        $parent = $this->productModelRepository->findOneByIdentifier($parentCode);

        if (null === $parent) {
            throw new \InvalidArgumentException(
                sprintf(
                    'The root product model "%s" does not exist',
                    $parentCode
                )
            );
        }

        $this->productBuilder
            ->withIdentifier($identifier)
            ->withParent($parentCode);

        foreach ($axisValues->getHash() as $axisValue) {
            foreach ($axisValue as $attributeCode => $value) {
                $this->productBuilder->withValue($attributeCode, $value);
            }
        }

        $variantProduct = $this->productBuilder->build();

        $this->productRepository->save($variantProduct);
    }

    /**
     * @param string $productIdentifier
     * @param string $productModelCode
     *
     * @When the parent of variant product :productIdentifier is changed for :productModelCode product model
     */
    public function changeVariantProductParent(string $productIdentifier, string $productModelCode): void
    {
        $product = $this->findProduct($productIdentifier);

        $this->productUpdater->update($product, ['parent' => $productModelCode]);
        $this->validateProduct($product);
        $this->productRepository->save($product);
    }

    /**
     * @param TableNode $table
     *
     * @When the parents of the following products are changed:
     */
    public function changeManyVariantProductsParents(TableNode $table): void
    {
        $products = [];
        foreach ($table->getHash() as $data) {
            $product = $this->findProduct($data['sku']);
            $this->productUpdater->update($product, ['parent' => $data['parent']]);
            $this->validateProduct($product);
            $products[] = $product;
        }

        $this->productRepository->saveAll($products);
    }

    /**
     * @param string $productIdentifier
     * @param string $productModelCode
     *
     * @When the parent of variant product :productIdentifier is changed for incorrect :productModelCode product model
     */
    public function setInvalidParent(string $productIdentifier, string $productModelCode): void
    {
        $product = $this->findProduct($productIdentifier);

        $this->productUpdater->update($product, ['parent' => $productModelCode]);

        try {
            $this->validateProduct($product);
        } catch (\InvalidArgumentException $e) {
            $this->exception = $e;
        }
    }

    /**
     * @param string $productIdentifier
     * @param string $productModelCode
     *
     * @Then the parent of the product :productIdentifier should be :productModelCode
     */
    public function productHasParent(string $productIdentifier, string $productModelCode): void
    {
        $product = $this->findProduct($productIdentifier);

        Assert::same($product->getParent()->getCode(), $productModelCode);
    }

    /**
     * @param string $productIdentifier
     * @param string $productModelCode
     *
     * @Then the parent of the product :productIdentifier should still be :productModelCode
     */
    public function productStillHasParent(string $productIdentifier, string $productModelCode): void
    {
        $this->productHasParent($productIdentifier, $productModelCode);
        Assert::isInstanceOf($this->exception, \InvalidArgumentException::class);
    }

    /**
     * @param string $identifier
     *
     * @throws \InvalidArgumentException
     *
     * @return ProductInterface
     */
    private function findProduct(string $identifier): ProductInterface
    {
        $product = $this->productRepository->findOneByIdentifier($identifier);
        if (null === $product) {
            throw new \InvalidArgumentException(sprintf('The product "%s" does not exist.', $identifier));
        }

        return $product;
    }

    /**
     * @param ProductInterface $product
     */
    private function validateProduct(ProductInterface $product)
    {
        $violations = $this->validator->validate($product);

        if (0 < $violations->count()) {
            $messages = [];
            foreach ($violations as $violation) {
                $messages[] = $violation->getMessage();
            }

            throw new \InvalidArgumentException(
                sprintf(
                    'Product "%s" is not valid, cf following constraint violations "%s"',
                    $product->getIdentifier(),
                    implode(', ', $messages)
                )
            );
        }
    }
}
