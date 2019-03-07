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

namespace Akeneo\Test\Pim\Automation\FranklinInsights\Acceptance\Context;

use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Model\ProductSubscription;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\ValueObject\SubscriptionId;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\ValueObject\SuggestedData;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\FakeClient;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Persistence\Repository\Memory\InMemoryAttributeOptionRepository;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Persistence\Repository\Memory\InMemoryIdentifiersMappingRepository;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Persistence\Repository\Memory\InMemoryProductSubscriptionRepository;
use Akeneo\Pim\Enrichment\Component\Product\Builder\ProductBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Factory\ValueCollectionFactoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModel;
use Akeneo\Pim\Structure\Component\Factory\FamilyFactory;
use Akeneo\Pim\Structure\Component\Model\AttributeOption;
use Akeneo\Test\Acceptance\Attribute\InMemoryAttributeRepository;
use Akeneo\Test\Acceptance\AttributeGroup\InMemoryAttributeGroupRepository;
use Akeneo\Test\Acceptance\Category\InMemoryCategoryRepository;
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

    /** @var EntityBuilder */
    private $attributeGroupBuilder;

    /** @var InMemoryProductSubscriptionRepository */
    private $subscriptionRepository;

    /** @var EntityBuilder */
    private $categoryBuilder;

    /** @var InMemoryCategoryRepository */
    private $categoryRepository;

    /** @var InMemoryAttributeOptionRepository */
    private $attributeOptionRepository;

    /** @var InMemoryIdentifiersMappingRepository */
    private $identifiersMappingRepository;

    /** @var FakeClient */
    private $fakeClient;

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
     * @param EntityBuilder $categoryBuilder
     * @param InMemoryCategoryRepository $categoryRepository
     * @param InMemoryAttributeOptionRepository $attributeOptionRepository
     * @param InMemoryIdentifiersMappingRepository $identifiersMappingRepository
     * @param FakeClient $fakeClient
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
        InMemoryProductSubscriptionRepository $subscriptionRepository,
        EntityBuilder $categoryBuilder,
        InMemoryCategoryRepository $categoryRepository,
        InMemoryAttributeOptionRepository $attributeOptionRepository,
        InMemoryIdentifiersMappingRepository $identifiersMappingRepository,
        FakeClient $fakeClient
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
        $this->categoryBuilder = $categoryBuilder;
        $this->categoryRepository = $categoryRepository;
        $this->attributeOptionRepository = $attributeOptionRepository;
        $this->identifiersMappingRepository = $identifiersMappingRepository;
        $this->fakeClient = $fakeClient;
    }

    /**
     * @param string $attributeOptionCodes
     * @param string $attributeCode
     *
     * @Given the predefined options :attributeOptionCodes for the attribute :attributeCode
     */
    public function thePredefinedAttributeOptions(string $attributeOptionCodes, string $attributeCode): void
    {
        $attribute = $this->attributeRepository->findOneByIdentifier($attributeCode);
        foreach ($this->toArray($attributeOptionCodes) as $attributeOptionCode) {
            $attributeOption = new AttributeOption();
            $attributeOption->setCode($attributeOptionCode);
            $attributeOption->setAttribute($attribute);
            $this->attributeOptionRepository->save($attributeOption);
        }
    }

    /**
     * @param string $attributes
     *
     * @Given /the predefined attributes? (.*)/
     */
    public function thePredefinedAttributes(string $attributes): void
    {
        $this->loadAttributes(array_map('strtolower', $this->toArray($attributes)));
    }

    /**
     * @param string $familyCode
     *
     * @Given the family ":familyCode"
     */
    public function theFamily(string $familyCode): void
    {
        $this->loadFamily($familyCode);
    }

    /**
     * @param string $identifier
     * @param string $familyCode
     *
     * @Given the product ":identifier" of the family ":familyCode"
     */
    public function theProductOfTheFamily(string $identifier, string $familyCode): void
    {
        $this->loadProduct($identifier, $familyCode);
    }

    /**
     * @param string $identifier
     * @param string $familyCode
     *
     * @Given the variant product ":identifier" of the family ":familyCode"
     */
    public function theVariantProductOfTheFamily(string $identifier, string $familyCode): void
    {
        $this->loadVariantProduct($identifier, $familyCode);
    }

    /**
     * @param string $identifier
     *
     * @Given the product without family ":identifier"
     */
    public function theProductWithoutFamily(string $identifier): void
    {
        $this->loadProduct($identifier, null);
    }

    /**
     * @Given the product :identifier has category :categoryCode
     *
     * @param string $identifier
     * @param string $categoryCode
     */
    public function theProductHasCategory(string $identifier, string $categoryCode): void
    {
        $product = $this->productRepository->findOneByIdentifier($identifier);
        if (in_array($categoryCode, $product->getCategoryCodes())) {
            return;
        }

        $category = $this->categoryRepository->findOneByIdentifier($categoryCode);
        if (null === $category) {
            $category = $this->categoryBuilder->build(['code' => $categoryCode]);
            $this->categoryRepository->save($category);
        }

        $product->addCategory($category);
        $this->productRepository->save($product);
    }

    /**
     * @param string $identifier
     *
     * @Given the product ":identifier" is subscribed to Franklin
     */
    public function theProductIsSubscribedToFranklin(string $identifier): void
    {
        $product = $this->productRepository->findOneByIdentifier($identifier);

        $subscription = new ProductSubscription($product->getId(), new SubscriptionId(uniqid()), ['sku' => '72527273070']);
        $this->subscriptionRepository->save($subscription);
    }

    /**
     * @Given there is suggested data for subscribed product :identifier
     *
     * @param string $identifier
     */
    public function thereIsSuggestedDataForSubscribedProduct(string $identifier): void
    {
        $this->theProductIsSubscribedToFranklin($identifier);
        $product = $this->productRepository->findOneByIdentifier($identifier);
        $subscription = $this->subscriptionRepository->findOneByProductId($product->getId());

        $suggestedData = $this->loadJsonFileAsArray(sprintf('suggested-data/suggested_data-%s.json', $identifier));
        $subscription->setSuggestedData(new SuggestedData($suggestedData));

        $this->subscriptionRepository->save($subscription);
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
                $rawValues[$attrCode] = [
                    '<all_channels>' => [
                        '<all_locales>' => $value,
                    ],
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

            if (!isset($familyData['code']) || '' === (string) $familyData['code']) {
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

    /**
     * @Given there are no more credits on my Franklin account
     */
    public function thereAreNoMoreCreditsOnMyAccount(): void
    {
        $this->fakeClient->disableCredit();
    }

    /**
     * @Given Franklin server is down
     */
    public function franklinServerIsDown(): void
    {
        $this->fakeClient->makeTheServerDown();
    }

    /**
     * @param string $attributeType
     * @param string $attributeCode
     * @param string $localeCode
     *
     * @Given the following :attributeType attribute ":attributeCode" specific to locale :localeCode
     */
    public function theFollowingAttributeSpecificToLocale(
        string $attributeType,
        string $attributeCode,
        string $localeCode
    ): void {
        if (null === $this->attributeGroupRepository->findOneByIdentifier('MANDATORY_ATTRIBUTE_GROUP_CODE')) {
            $group = $this->attributeGroupBuilder->build(['code' => 'MANDATORY_ATTRIBUTE_GROUP_CODE']);
            $this->attributeGroupRepository->save($group);
        }

        $attribute = $this->attributeBuilder->build([
            'code' => $attributeCode,
            'type' => 'pim_catalog_' . $attributeType,
            'group' => 'MANDATORY_ATTRIBUTE_GROUP_CODE',
            'available_locales' => [$localeCode],
        ]);
        $this->attributeRepository->save($attribute);
    }

    /**
     * @Given I delete the attribute mapped to :franklinIdentifier
     *
     * @param string $franklinIdentifier
     */
    public function iDeleteTheAttributeMappedTo(string $franklinIdentifier): void
    {
        $identifiersMapping = $this->identifiersMappingRepository->find();
        $identifiersMapping->map($franklinIdentifier, null);
        $this->identifiersMappingRepository->save($identifiersMapping);
    }

    /**
     * Loads attributes according to a provided list of attribute codes and a default attribute group.
     * Fixture content is in a file in "Resources/config/fixtures/attributes/".
     *
     * @param array $attributeCodes
     */
    private function loadAttributes(array $attributeCodes): void
    {
        $normalizedAttributes = $this->loadJsonFileAsArray('attributes/attributes.json');

        $attributeGroup = $this->attributeGroupBuilder->build(['code' => 'other']);
        $this->attributeGroupRepository->save($attributeGroup);

        foreach ($attributeCodes as $attributeCode) {
            $attribute = $this->attributeBuilder->build($normalizedAttributes[$attributeCode]);
            $this->attributeRepository->save($attribute);
        }
    }

    /**
     * Loads the family with its attributes
     * Fixture content is in a file in Resources/config/fixtures/families/.
     *
     * @param string $familyCode
     */
    private function loadFamily(string $familyCode): void
    {
        $normalizedFamily = $this->loadJsonFileAsArray(sprintf('families/family-%s.json', $familyCode));

        $this->loadAttributes($normalizedFamily['attributes']);

        $family = $this->familyBuilder->build($normalizedFamily);
        $this->familyRepository->save($family);
    }

    /**
     * Loads a product with its family (if any) and attributes.
     * Fixture content is in a JSON file in "Resources/config/fixtures/products/".
     *
     * @param string $identifier
     * @param string|null $familyCode
     */
    private function loadProduct(string $identifier, ?string $familyCode = null): void
    {
        if (null !== $familyCode) {
            $normalizedProduct = $this->loadJsonFileAsArray(sprintf(
                'products/product-%s-%s.json',
                $familyCode,
                $identifier
            ));
            $this->loadFamily($familyCode);
        } else {
            $normalizedProduct = $this->loadJsonFileAsArray(sprintf(
                'products/product-%s.json',
                $identifier
            ));
            $this->loadProductAttributes($normalizedProduct);
        }

        $product = $this->productBuilder->createProduct($identifier, $familyCode);
        $this->setValuesFromRawDataToProduct($product, $normalizedProduct);

        $this->productRepository->save($product);
    }

    /**
     * Loads a variant product with its family and attributes.
     * Fixture content is in a JSON file in "Resources/config/fixtures/products/".
     *
     * @param string $identifier
     * @param string $familyCode
     */
    private function loadVariantProduct(string $identifier, string $familyCode): void
    {
        $normalizedProduct = $this->loadJsonFileAsArray(sprintf(
            'products/product-%s-%s.json',
            $familyCode,
            $identifier
        ));
        $this->loadFamily($familyCode);

        $product = $this->productBuilder->createProduct($identifier, $familyCode);
        $this->setValuesFromRawDataToProduct($product, $normalizedProduct);
        $product->setParent(new ProductModel());

        $this->productRepository->save($product);
    }

    /**
     * Loads the attributes of a product, using the values of the product.
     *
     * @param array $normalizedProduct
     */
    private function loadProductAttributes(array $normalizedProduct): void
    {
        $attributeCodes = array_keys($normalizedProduct['values']);
        $this->loadAttributes(array_merge(['sku'], $attributeCodes));
    }

    /**
     * Converts raw data (storage format) into an array of values, and set the values to a product.
     *
     * @param ProductInterface $product
     * @param array $normalizedProduct
     */
    private function setValuesFromRawDataToProduct(ProductInterface $product, array $normalizedProduct): void
    {
        $rawValues = [];
        foreach ($normalizedProduct['values'] as $attrCode => $value) {
            $rawValues[$attrCode] = [
                '<all_channels>' => [
                    '<all_locales>' => $value[0]['data'],
                ],
            ];
        }

        $values = $this->valueCollectionFactory->createFromStorageFormat($rawValues);
        $product->setValues($values);
    }

    /**
     * Loads a file containing json content and return it as a PHP array.
     *
     * @param string $filepath
     *
     * @return array
     */
    private function loadJsonFileAsArray(string $filepath): array
    {
        $filepath = realpath(sprintf(__DIR__ . '/../Resources/fixtures/%s', $filepath));
        Assert::true(file_exists($filepath));
        $jsonContent = file_get_contents($filepath);

        return json_decode($jsonContent, true);
    }

    /**
     * @param string $list
     *
     * @return array
     */
    private function toArray(string $list): array
    {
        if (empty($list)) {
            return [];
        }

        return explode(', ', str_replace(' and ', ', ', $list));
    }
}
