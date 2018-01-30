<?php

namespace Pim\Behat\Context\Storage;

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Assert;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\VariantProductInterface;
use Pim\Component\Catalog\Repository\ProductModelRepositoryInterface;
use Pim\Component\Catalog\Repository\ProductRepositoryInterface;
use Pim\Component\Connector\ArrayConverter\FlatToStandard\Product\AttributeColumnInfoExtractor;

class ProductStorage implements Context
{
    /** @var AttributeColumnInfoExtractor */
    private $attributeColumnInfoExtractor;

    /** @var ProductRepositoryInterface */
    private $productRepository;

    /** @var ProductModelRepositoryInterface */
    private $productModelRepository;

    /** @var EntityManagerInterface */
    private $entityManager;

    /**
     * @param AttributeColumnInfoExtractor    $attributeColumnInfoExtractor
     * @param ProductRepositoryInterface      $productRepository
     * @param ProductModelRepositoryInterface $productModelRepository
     * @param EntityManagerInterface          $entityManager
     */
    public function __construct(
        AttributeColumnInfoExtractor $attributeColumnInfoExtractor,
        ProductRepositoryInterface $productRepository,
        ProductModelRepositoryInterface $productModelRepository,
        EntityManagerInterface $entityManager
    ) {
        $this->attributeColumnInfoExtractor = $attributeColumnInfoExtractor;
        $this->productRepository = $productRepository;
        $this->productModelRepository = $productModelRepository;
        $this->entityManager = $entityManager;
    }

    /**
     * @param string    $identifier
     * @param TableNode $table
     *
     * @throws \Exception
     *
     * @Given /^the product "([^"]*)" should not have the following values?:$/
     */
    public function theProductShouldNotHaveTheFollowingValues($identifier, TableNode $table)
    {
        $product = $this->productRepository->findOneByIdentifier($identifier);

        $rows = $table->getRowsHash();
        foreach (array_keys($rows) as $rawCode) {
            $info = $this->attributeColumnInfoExtractor->extractColumnInfo($rawCode);

            $attribute     = $info['attribute'];
            $attributeCode = $attribute->getCode();
            $localeCode    = $info['locale_code'];
            $scopeCode     = $info['scope_code'];
            $productValue  = $product->getValue($attributeCode, $localeCode, $scopeCode);

            if (null !== $productValue) {
                throw new \Exception(sprintf('Product value for product "%s" exists', $identifier));
            }
        }
    }

    /**
     * @param string $productIdentifier
     * @param string $parentCode
     *
     * @Given the parent of the product :productIdentifier should be :parentCode
     */
    public function productHaveParent(string $productIdentifier, string $parentCode)
    {
        $this->entityManager->clear();

        /** @var VariantProductInterface $product */
        $product = $this->productRepository->findOneByIdentifier($productIdentifier);

        Assert::assertEquals($product->getParent()->getCode(), $parentCode);
    }

    /**
     * @param string    $identifier
     * @param TableNode $table
     *
     * @throws \Exception
     *
     * @Given /^the variant product "([^"]*)" should not have the following values?:$/
     */
    public function theVariantProductShouldNotHaveTheFollowingValues(string $identifier, TableNode $table)
    {
        $this->entityManager->clear();

        /** @var VariantProductInterface $product */
        $product = $this->productRepository->findOneByIdentifier($identifier);

        $rows = $table->getRowsHash();
        foreach (array_keys($rows) as $rawCode) {
            $infos = $this->attributeColumnInfoExtractor->extractColumnInfo($rawCode);

            $attribute = $infos['attribute'];
            $attributeCode = $attribute->getCode();
            $productValue = $product
                ->getValuesForVariation()
                ->getByCodes($attributeCode, $infos['locale_code'], $infos['scope_code']);

            if (null !== $productValue) {
                throw new \Exception(sprintf('Product value for variant product "%s" exists', $identifier));
            }
        }
    }

    /**
     * @param string $productIdentifier
     *
     * @throws \Exception
     *
     * @Then :productIdentifier should be a product
     */
    public function productShouldNotHaveAParent(string $productIdentifier): void
    {
        $product = $this->productRepository->findOneByIdentifier($productIdentifier);

        if (null === $product) {
            throw new \Exception(sprintf('The product "%s" does not exist', $productIdentifier));
        }

        if (!$product instanceof ProductInterface || $productIdentifier instanceof VariantProductInterface) {
            throw new \Exception(
                sprintf('The given object must be a variant product, %s given', ClassUtils::getClass($product))
            );
        }
    }
}
