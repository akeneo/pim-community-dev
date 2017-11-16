<?php

declare(strict_types=1);

namespace Pim\Behat\Context\Storage;

use Behat\Behat\Context\Context;
use Pim\Component\Catalog\Model\VariantProductInterface;
use Pim\Component\Catalog\Repository\ProductRepositoryInterface;

final class VariantProductStorage implements Context
{
    /** @var ProductRepositoryInterface */
    private $variantProductRepository;

    /**
     * @param ProductRepositoryInterface $variantProductRepository
     */
    public function __construct(ProductRepositoryInterface $variantProductRepository)
    {
        $this->variantProductRepository = $variantProductRepository;
    }

    /**
     * @Then the parent of :variantProductIdentifier should be :productModelCode
     */
    public function theParentOfShouldBe(string $variantProductIdentifier, string $productModelCode): void
    {
        /** @var VariantProductInterface $variantProduct */
        $variantProduct = $this->variantProductRepository->findOneByIdentifier($variantProductIdentifier);

        if (null === $variantProduct) {
            throw new \Exception(sprintf('The variant product "%s" does not exist', $variantProductIdentifier));
        }

        if (null === $productModel = $variantProduct->getParent()) {
            throw new \Exception(sprintf('The variant product "%s" does not have parent', $variantProductIdentifier));
        }

        if ($productModelCode !== $expectedProductModelCode = $productModel->getCode()) {
            throw new \Exception(
                sprintf(
                    'Expected parent code "%s", given parent code "%s"',
                    $productModelCode,
                    $expectedProductModelCode
                )
            );
        }
    }

    /**
     * @Then the variant family of :variantProductIdentifier should be :familyVariantCode
     */
    public function theFamilyVariantOfShouldBe(string $variantProductIdentifier, string $familyVariantCode): void
    {
        /** @var VariantProductInterface $variantProduct */
        $variantProduct = $this->variantProductRepository->findOneByIdentifier($variantProductIdentifier);

        if (null === $variantProduct) {
            throw new \Exception(sprintf('The variant product "%s" does not exist', $variantProductIdentifier));
        }

        if (null === $familyVariant = $variantProduct->getFamilyVariant()) {
            throw new \Exception(
                sprintf('The variant product "%s" does not have family variant', $variantProductIdentifier)
            );
        }

        if ($familyVariantCode !== $expectedProductModelCode = $familyVariant->getCode()) {
            throw new \Exception(
                sprintf(
                    'Expected family variant code "%s", given family variant code "%s"',
                    $familyVariantCode,
                    $expectedProductModelCode
                )
            );
        }
    }

    /**
     * @Then the variant product :variantProductIdentifier should only own the following values :values
     */
    public function theVariantProductShouldNotHaveValue(string $variantProductIdentifier, string $valueCodes): void
    {
        /** @var VariantProductInterface $variantProduct */
        $variantProduct = $this->variantProductRepository->findOneByIdentifier($variantProductIdentifier);

        if (null === $variantProduct) {
            throw new \Exception(sprintf('The variant product "%s" does not exist', $variantProductIdentifier));
        }

        $valueCodes = array_map('trim', explode(',', $valueCodes));
        $attributeCodes = $variantProduct->getValuesForVariation()->getAttributesKeys();
        $diff = array_diff($attributeCodes, $valueCodes);

        sort($diff);
        sort($attributeCodes);

        if ($diff !== $attributeCodes) {
            throw new \Exception(
                sprintf(
                    'The variant product should only own the following value "%s" but got %s',
                    implode(',', $diff),
                    implode(',', $attributeCodes)
                )
            );
        }
    }
}
