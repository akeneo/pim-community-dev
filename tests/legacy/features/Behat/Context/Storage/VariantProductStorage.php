<?php

declare(strict_types=1);

namespace Pim\Behat\Context\Storage;

use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Behat\Behat\Context\Context;
use Doctrine\Common\Util\ClassUtils;

final class VariantProductStorage implements Context
{
    /** @var ProductRepositoryInterface */
    private $productRepository;

    /**
     * @param ProductRepositoryInterface $variantProductRepository
     */
    public function __construct(ProductRepositoryInterface $variantProductRepository)
    {
        $this->productRepository = $variantProductRepository;
    }

    /**
     * @Then the variant family of :variantProductIdentifier should be :familyVariantCode
     */
    public function theFamilyVariantOfShouldBe(string $variantProductIdentifier, string $familyVariantCode): void
    {
        $variantProduct = $this->productRepository->findOneByIdentifier($variantProductIdentifier);

        if (null === $variantProduct) {
            throw new \Exception(sprintf('The variant product "%s" does not exist', $variantProductIdentifier));
        }

        if (!$variantProduct->isVariant()) {
            throw new \Exception(
                sprintf('The given object must be a variant product, %s given', ClassUtils::getClass($variantProduct))
            );
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
        $variantProduct = $this->productRepository->findOneByIdentifier($variantProductIdentifier);

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
