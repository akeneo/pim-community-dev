<?php

declare(strict_types=1);

namespace Akeneo\Test\Acceptance\Product;
use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use Pim\Component\Catalog\Builder\ProductBuilderInterface;
use Pim\Component\Catalog\Factory\ValueCollectionFactoryInterface;

/**
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class ProductContext implements Context
{
    /** @var InMemoryProductRepository */
    private $productRepository;

    /** @var ProductBuilderInterface */
    private $productBuilder;

    /** @var ValueCollectionFactoryInterface */
    private $valueCollectionFactory;

    /**
     * @param InMemoryProductRepository $productRepository
     * @param ProductBuilderInterface $productBuilder
     * @param ValueCollectionFactoryInterface $valueCollectionFactory
     */
    public function __construct(
        InMemoryProductRepository $productRepository,
        ProductBuilderInterface $productBuilder,
        ValueCollectionFactoryInterface $valueCollectionFactory
    ) {
        $this->productRepository = $productRepository;
        $this->productBuilder = $productBuilder;
        $this->valueCollectionFactory = $valueCollectionFactory;
    }

    /**
     * @Given the following product:
     */
    public function theFollowingProduct(TableNode $table)
    {
        foreach ($table->getHash() as $productRow) {
            $product = $this->productBuilder->createProduct($productRow['identifier'], $productRow['family']);
            unset($productRow['identifier'], $productRow['family']);

            $rawValues = [];
            foreach ($productRow as $attrCode => $value) {
                $rawValues[$attrCode] =[
                    '<all_channels>' => [
                        '<all_locales>' => $value
                    ]
                ];


            }
            $values = $this->valueCollectionFactory->createFromStorageFormat($rawValues);
            $product->setValues($values);

            $this->productRepository->save($product);
        }
    }
}
