<?php

declare(strict_types=1);

namespace Pim\Behat\Context\Storage;

use Behat\Behat\Context\Context;
use Pim\Component\Catalog\Model\VariantProductInterface;
use Pim\Component\Catalog\Repository\ProductRepositoryInterface;

class VariantProductStorage implements Context
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
}
