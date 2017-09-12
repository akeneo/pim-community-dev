<?php

namespace Pim\Behat\Context\Storage;

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Pim\Behat\Context\PimContext;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Pim\Component\Catalog\Model\VariantProductInterface;
use Pim\Component\Catalog\Repository\ProductRepositoryInterface;
use Pim\Component\Connector\ArrayConverter\FlatToStandard\Product\AttributeColumnInfoExtractor;

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
        $this->entityManager->clear();

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
    public function productHaveParent(string $productIdentifier, string $parentCode)
    {
        $this->entityManager->clear();

        /** @var VariantProductInterface $product */
        $product = $this->productRepository->findOneByIdentifier($productIdentifier);

        assertEquals($product->getParent()->getCode(), $parentCode);
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
}
