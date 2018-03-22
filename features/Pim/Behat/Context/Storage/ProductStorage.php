<?php

namespace Pim\Behat\Context\Storage;

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityManagerInterface;
use Pim\Component\Catalog\Exception\ObjectNotFoundException;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ValueInterface;
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
        $valueGetter = function (ProductInterface $product, string $attributeCode, array $infos): ?ValueInterface {
            return $product->getValues()->getByCodes($attributeCode, $infos['locale_code'], $infos['scope_code']);
        };

        $assertion = function (AttributeInterface $attribute, ?ValueInterface $value) use ($identifier) {
            if (null !== $value) {
                throw new \Exception(sprintf('The attribute "%s" for product "%s" exists', $attribute->getCode(), $identifier));
            }
        };

        $this->assertOnTheFollowingAttributes($identifier, $table, $valueGetter, $assertion);
    }

    /**
     * @param $identifier
     * @param TableNode $attributeCodes
     * @throws ObjectNotFoundException
     *
     * @Then /^the product "([^"]*)" should have the following attribute codes?:$/
     */
    public function theProductShouldHaveTheFollowingAttributes($identifier, TableNode $attributeCodes)
    {
        $this->entityManager->clear();

        $valueGetter = function (ProductInterface $product, string $attributeCode, array $infos): ?ValueInterface {
            return $product->getValues()->getByCodes($attributeCode, $infos['locale_code'], $infos['scope_code']);
        };

        $assertion = function (AttributeInterface $attribute, ?ValueInterface $value) use ($identifier) {
            if (null === $value) {
                throw new \Exception(sprintf('The attribute "%s" for product "%s" does not exist', $attribute->getCode(), $identifier));
            }
        };

        $this->assertOnTheFollowingAttributes($identifier, $attributeCodes, $valueGetter, $assertion);
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

        $valuesGetter = function (VariantProductInterface $product, string $attributeCode, array $infos): ?ValueInterface {
            return $product->getValuesForVariation()->getByCodes($attributeCode, $infos['locale_code'], $infos['scope_code']);
        };

        $assertion = function (AttributeInterface $attribute, ?ValueInterface $value) use ($identifier) {
            if (null !== $value) {
                throw new \Exception(sprintf('Attribute %s for variant-product "%s" exists', $attribute->getCode(), $identifier));
            }
        };

        $this->assertOnTheFollowingAttributes($identifier, $table, $valuesGetter, $assertion);
    }

    /**
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

    /**
     * @param string $identifier
     * @param TableNode $attributeCodes
     * @param callable $valueGetter(ProductInterface $product, string $attributeCode, array $infos)
     * @param callable $assertion(AttributeInterface $attribute, ?ValueInterface $value)
     *
     * @throws ObjectNotFoundException
     */
    private function assertOnTheFollowingAttributes(
        string $identifier,
        TableNode $attributeCodes,
        callable $valueGetter,
        callable $assertion
    ): void {
        $product = $this->productRepository->findOneByIdentifier($identifier);

        if (null === $product) {
            throw new ObjectNotFoundException(
                sprintf('The product with identifier %s does not exist', $identifier)
            );
        }

        foreach ($attributeCodes->getRowsHash() as $rawCode => $value) {
            $infos = $this->attributeColumnInfoExtractor->extractColumnInfo($rawCode);

            /** @var AttributeInterface $attribute */
            $attribute = $infos['attribute'];

            $productValue = $valueGetter($product, $attribute->getCode(), $infos);

            $assertion($attribute, $productValue);
        }
    }
}
