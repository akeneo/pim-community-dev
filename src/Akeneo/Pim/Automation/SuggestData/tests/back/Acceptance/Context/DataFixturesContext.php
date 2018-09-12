<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Test\Pim\Automation\SuggestData\Acceptance\Context;

use Akeneo\Pim\Automation\SuggestData\Domain\Model\ProductSubscription;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Repository\Memory\InMemoryProductSubscriptionRepository;
use Akeneo\Pim\Enrichment\Component\Product\Builder\ProductBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Factory\ValueCollectionFactoryInterface;
use Akeneo\Pim\Structure\Component\Factory\AttributeFactory;
use Akeneo\Pim\Structure\Component\Factory\FamilyFactory;
use Akeneo\Pim\Structure\Component\Updater\AttributeUpdater;
use Akeneo\Pim\Structure\Component\Updater\FamilyUpdater;
use Akeneo\Test\Acceptance\Attribute\InMemoryAttributeRepository;
use Akeneo\Test\Acceptance\AttributeGroup\InMemoryAttributeGroupRepository;
use Akeneo\Test\Acceptance\Family\InMemoryFamilyRepository;
use Akeneo\Test\Acceptance\Product\InMemoryProductRepository;
use Akeneo\Test\Common\EntityBuilder;
use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use Webmozart\Assert\Assert;

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

    /** @var EntityBuilder */
    private $attributeBuilder;

    /** @var EntityBuilder */
    private $familyBuilder;

    /** @var InMemoryAttributeGroupRepository */
    private $attributeGroupRepository;

    /** @var AttributeGroupBuilder */
    private $attributeGroupBuilder;

    /** @var InMemoryProductSubscriptionRepository */
    private $subscriptionRepository;

    /**
     * @param InMemoryProductRepository $productRepository
     * @param ProductBuilderInterface $productBuilder
     * @param ValueCollectionFactoryInterface $valueCollectionFactory
     * @param InMemoryFamilyRepository $familyRepository
     * @param FamilyFactory $familyFactory
     * @param InMemoryAttributeRepository $attributeRepository
     * @param EntityBuilder $familyBuilder
     * @param EntityBuilder $attributeBuilder
     * @param InMemoryAttributeGroupRepository $attributeGroupRepository
     * @param EntityBuilder $attributeGroupBuilder
     * @param InMemoryProductSubscriptionRepository $subscriptionRepository
     */
    public function __construct(
        InMemoryProductRepository $productRepository,
        ProductBuilderInterface $productBuilder,
        ValueCollectionFactoryInterface $valueCollectionFactory,
        InMemoryFamilyRepository $familyRepository,
        FamilyFactory $familyFactory,
        InMemoryAttributeRepository $attributeRepository,
        EntityBuilder $familyBuilder,
        EntityBuilder $attributeBuilder,
        InMemoryAttributeGroupRepository $attributeGroupRepository,
        EntityBuilder $attributeGroupBuilder,
        InMemoryProductSubscriptionRepository $subscriptionRepository
    ) {
        $this->productRepository = $productRepository;
        $this->productBuilder = $productBuilder;
        $this->valueCollectionFactory = $valueCollectionFactory;
        $this->familyRepository = $familyRepository;
        $this->familyFactory = $familyFactory;
        $this->attributeRepository = $attributeRepository;
        $this->familyBuilder = $familyBuilder;
        $this->attributeBuilder = $attributeBuilder;
        $this->attributeGroupBuilder = $attributeGroupBuilder;
        $this->attributeGroupRepository = $attributeGroupRepository;
        $this->subscriptionRepository = $subscriptionRepository;
    }

    /**
     * @Given the product ":identifier" of the family ":familyCode"
     */
    public function theProductOfTheFamily(string $identifier, string $familyCode): void
    {
        $this->loadProduct($identifier, $familyCode);
    }

    /**
     * @Given the product ":identifier" is subscribed to PIM.ai
     */
    public function theProductIsSubscribedToPimAi($identifier): void
    {
        $product = $this->productRepository->findOneByIdentifier($identifier);

        $subscription = new ProductSubscription($product, uniqid());
        $this->subscriptionRepository->save($subscription);
    }

    /**
     * Loads attributes for a specific family and a default attribute group
     * Fixture content is in a file in Resources/config/fixtures/attributes/
     *
     * @param string $familyCode
     */
    private function loadFamilyAttributes(string $familyCode): void
    {
        $data = $this->loadJsonFileAsArray(sprintf('attributes/attributes-family-%s.yml', $familyCode));

        $attributeGroup = $this->attributeGroupBuilder->build(['code' => 'other']);
        $this->attributeGroupRepository->save($attributeGroup);

        foreach ($data as $rowData) {
            $attribute = $this->attributeBuilder->build($rowData);
            $this->attributeRepository->save($attribute);
        }
    }

    /**
     * Loads the family with its attributes
     * Fixture content is in a file in Resources/config/fixtures/families/
     *
     * @param string $familyCode
     */
    private function loadFamily(string $familyCode): void
    {
        $this->loadFamilyAttributes($familyCode);

        $data = $this->loadJsonFileAsArray(sprintf('families/family-%s.yml', $familyCode));

        $family = $this->familyBuilder->build($data);
        $this->familyRepository->save($family);
    }

    /**
     * Loads a product with its family and attributes
     * Fixture content is in a file in Resources/config/fixtures/products/
     *
     * @param string $identifier
     * @param string $familyCode
     */
    private function loadProduct(string $identifier, string $familyCode): void
    {
        $this->loadFamily($familyCode);

        $data = $this->loadJsonFileAsArray(sprintf('products/product-%s-%s.yml', $familyCode, $identifier));

        $product = $this->productBuilder->createProduct($identifier, $familyCode);
        $rawValues = [];
        foreach ($data['values'] as $attrCode => $value) {
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

    /**
     * Loads a file containing json content and return it as a PHP array
     *
     * @param $filepath
     *
     * @return array
     */
    private function loadJsonFileAsArray($filepath)
    {
        $filepath = realpath(sprintf(__DIR__ .'/../Resources/fixtures/%s', $filepath));
        Assert::true(file_exists($filepath));
        $jsonContent = file_get_contents($filepath);

        return json_decode($jsonContent, true);
    }

    /**
     * @Given the following product:
     *
     * @param TableNode $table
     */
    public function theFollowingProduct(TableNode $table): void
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
     *
     * @param TableNode $table
     *
     * @throws \Exception
     */
    public function theFollowingFamily(TableNode $table): void
    {
        foreach ($table->getHash() as $familyData) {
            $family = $this->familyFactory->create();

            if (!isset($familyData['code']) || '' === (string)$familyData['code']) {
                throw new \Exception('Missing required field code for family creation');
            }
            $family->setCode($familyData['code']);

            $attributeCodes = explode(',', $familyData['attributes']);
            foreach ($attributeCodes as $attributeCode) {
                $attribute = $this->attributeRepository->findOneByIdentifier($attributeCode);
                if (null === $attribute) {
                    throw new \Exception(sprintf('Attribute "%s" does not exist', $attributeCode));
                }
                $family->addAttribute($attribute);
            }

            if (isset($familyData['label-en_US'])) {
                $family->setLocale('en-US')->setLabel($familyData['label-en_US']);
            }

            $this->familyRepository->save($family);
        }
    }
}
