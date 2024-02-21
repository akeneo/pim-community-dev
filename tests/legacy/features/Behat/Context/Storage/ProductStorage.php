<?php

namespace Pim\Behat\Context\Storage;

use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\AttributeColumnInfoExtractor;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Assert;

class ProductStorage implements Context
{
    /** @var AttributeColumnInfoExtractor */
    private $attributeColumnInfoExtractor;

    /** @var ProductRepositoryInterface */
    private $productRepository;

    /** @var EntityManagerInterface */
    private $entityManager;

    /**
     * @param AttributeColumnInfoExtractor $attributeColumnInfoExtractor
     * @param ProductRepositoryInterface   $productRepository
     * @param EntityManagerInterface       $entityManager
     */
    public function __construct(
        AttributeColumnInfoExtractor $attributeColumnInfoExtractor,
        ProductRepositoryInterface $productRepository,
        EntityManagerInterface $entityManager
    ) {
        $this->attributeColumnInfoExtractor = $attributeColumnInfoExtractor;
        $this->productRepository = $productRepository;
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
        /** @var ProductInterface $product */
        $product = $this->productRepository->findOneByIdentifier($identifier);

        foreach ($table->getRowsHash() as $rawCode => $value) {
            $infos = $this->attributeColumnInfoExtractor->extractColumnInfo($rawCode);

            $attribute     = $infos['attribute'];
            $attributeCode = $attribute->getCode();
            $localeCode    = $infos['locale_code'];
            $scopeCode     = $infos['scope_code'];
            $productValue  = $product->getValue($attributeCode, $localeCode, $scopeCode);

            if (null !== $productValue) {
                throw new \Exception(sprintf('Product value for product "%s" exists', $identifier));
            }
        }
    }

    /**
     * @Given the parent of the product :productIdentifier should be :parentCode
     */
    public function productHasParent(string $productIdentifier, string $parentCode)
    {
        $this->entityManager->clear();
        $product = $this->productRepository->findOneByIdentifier($productIdentifier);

        Assert::assertEquals($parentCode, $product->getParent()->getCode());
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
        $product = $this->productRepository->findOneByIdentifier($identifier);

        foreach ($table->getRowsHash() as $rawCode => $value) {
            $infos = $this->attributeColumnInfoExtractor->extractColumnInfo($rawCode);

            $attribute = $infos['attribute'];
            $attributeCode = $attribute->getCode();
            $productValue = $product->getValuesForVariation()->getByCodes($attributeCode, $infos['locale_code'], $infos['scope_code']);

            if (null !== $productValue) {
                throw new \Exception(sprintf('Product value for product "%s" exists', $identifier));
            }
        }
    }

    /**
     * @Then :productIdentifier should be a product
     */
    public function shouldBeProduct(string $productIdentifier): void
    {
        $product = $this->productRepository->findOneByIdentifier($productIdentifier);

        if (null === $product) {
            throw new \Exception(sprintf('The product "%s" does not exist', $productIdentifier));
        }

        Assert::isFalse($product->isVariant());
    }

    /**
     * @Then :productIdentifier should be a variant product
     */
    public function shouldBeVariantProduct(string $productIdentifier): void
    {
        $product = $this->productRepository->findOneByIdentifier($productIdentifier);

        if (null === $product) {
            throw new \Exception(sprintf('The product "%s" does not exist', $productIdentifier));
        }

        Assert::isTrue($product->isVariant());
    }
}
