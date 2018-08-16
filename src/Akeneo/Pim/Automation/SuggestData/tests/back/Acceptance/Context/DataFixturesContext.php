<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\SuggestData\Acceptance\Context;

use Akeneo\Pim\Enrichment\Component\Product\Builder\ProductBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Factory\ValueCollectionFactoryInterface;
use Akeneo\Pim\Structure\Component\Factory\FamilyFactory;
use Akeneo\Test\Acceptance\Attribute\InMemoryAttributeRepository;
use Akeneo\Test\Acceptance\Family\InMemoryFamilyRepository;
use Akeneo\Test\Acceptance\Product\InMemoryProductRepository;
use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;

/**
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class DataFixturesContext implements Context
{
    /** @var InMemoryAttributeRepository */
    private $attributeRepository;

    /** @var InMemoryFamilyRepository */
    private $familyRepository;

    /** @var FamilyFactory */
    private $familyFactory;

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
     * @param InMemoryFamilyRepository $familyRepository
     * @param FamilyFactory $familyFactory
     * @param InMemoryAttributeRepository $attributeRepository
     */
    public function __construct(
        InMemoryProductRepository $productRepository,
        ProductBuilderInterface $productBuilder,
        ValueCollectionFactoryInterface $valueCollectionFactory,
        InMemoryFamilyRepository $familyRepository,
        FamilyFactory $familyFactory,
        InMemoryAttributeRepository $attributeRepository
    ) {
        $this->productRepository = $productRepository;
        $this->productBuilder = $productBuilder;
        $this->valueCollectionFactory = $valueCollectionFactory;
        $this->familyRepository = $familyRepository;
        $this->familyFactory = $familyFactory;
        $this->attributeRepository = $attributeRepository;
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

    /**
     * @Given the following family:
     */
    public function theFollowingFamily(TableNode $table)
    {
        foreach ($table->getHash() as $familyData) {
            $family = $this->familyFactory->create();

            $attributeCodes = explode(',', $familyData['attributes']);
            foreach ($attributeCodes as $attributeCode) {
                $attribute = $this->attributeRepository->findOneByIdentifier($attributeCode);
                if (null === $attribute) {
                    throw new \Exception(sprintf('Attribute "%s" does not exist', $attributeCode));
                }
                $family->addAttribute($attribute);
            }

            $this->familyRepository->save($family);
        }
    }
}
