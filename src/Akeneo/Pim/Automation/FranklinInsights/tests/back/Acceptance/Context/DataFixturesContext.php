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

use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\ProductId;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Model\ProductSubscription;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\ValueObject\SubscriptionId;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\ValueObject\SuggestedData;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\FakeClient;
use Akeneo\Pim\Enrichment\Component\Product\Builder\ProductBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModel;
use Akeneo\Pim\Enrichment\Component\Product\Value\ScalarValue;
use Akeneo\Pim\Structure\Component\Factory\FamilyFactory;
use Akeneo\Pim\Structure\Component\Model\AttributeOption;
use Akeneo\Test\Acceptance\Attribute\InMemoryAttributeRepository;
use Akeneo\Test\Acceptance\AttributeGroup\InMemoryAttributeGroupRepository;
use Akeneo\Test\Acceptance\AttributeOption\InMemoryAttributeOptionRepository;
use Akeneo\Test\Acceptance\Category\InMemoryCategoryRepository;
use Akeneo\Test\Acceptance\Family\InMemoryFamilyRepository;
use Akeneo\Test\Acceptance\Product\InMemoryProductRepository;
use Akeneo\Test\Common\EntityBuilder;
use Akeneo\Test\Pim\Automation\FranklinInsights\Acceptance\Persistence\InMemory\Repository\InMemoryIdentifiersMappingRepository;
use Akeneo\Test\Pim\Automation\FranklinInsights\Acceptance\Persistence\InMemory\Repository\InMemoryProductSubscriptionRepository;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Behat\Behat\Context\Context;
use Behat\Behat\Tester\Exception\PendingException;
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

    /** @var ObjectUpdaterInterface */
    private $productUpdater;

    /** @var EntityBuilder */
    private $optionBuilder;

    public function __construct(
        InMemoryProductRepository $productRepository,
        ProductBuilderInterface $productBuilder,
        ObjectUpdaterInterface $productUpdater,
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
        FakeClient $fakeClient,
        EntityBuilder $optionBuilder
    ) {
        $this->productRepository = $productRepository;
        $this->productBuilder = $productBuilder;
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
        $this->productUpdater = $productUpdater;
        $this->optionBuilder = $optionBuilder;
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
     * @Given the attribute group :attrGroupCode
     */
    public function theAttributeGroup($attrGroupCode): void
    {
        $attrGroup = $this->attributeGroupBuilder->build(['code' => $attrGroupCode]);
        $this->attributeGroupRepository->save($attrGroup);
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

        $subscription = new ProductSubscription(new ProductId($product->getId()), new SubscriptionId(uniqid()), ['sku' => '72527273070']);
        $this->subscriptionRepository->save($subscription);
    }

    /**
     * @Given the product ":identifier" that has the same ASIN that the product ":existingIdentifier"
     */
    public function theProductThatHasTheSameAsinThatTheProduct(string $identifier)
    {
        $this->loadProduct($identifier, 'router');
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
        $subscription = $this->subscriptionRepository->findOneByProductId(new ProductId($product->getId()));

        $suggestedData = $this->loadJsonFileAsArray(sprintf('suggested-data/suggested_data-%s.json', $identifier));
        $subscription->setSuggestedData(new SuggestedData($suggestedData));

        $this->subscriptionRepository->save($subscription);
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
     * @Given the attribute :attributeCode is not in family :familyCode
     */
    public function theAttributeIsNotInFamily($attributeCode, $familyCode)
    {
        $family = $this->familyRepository->findOneByIdentifier($familyCode);

        if ($family->hasAttributeCode($attributeCode))
        {
            $attribute = $this->attributeRepository->findOneByIdentifier($attributeCode);
            $family->removeAttribute($attribute);
            $this->familyRepository->save($family);

        }
    }

    /**
     * Loads attributes according to a provided list of attribute codes and a default attribute group.
     * Fixture content is in a file in "Resources/config/fixtures/attributes/".
     *
     * @param array $attributeCodes
     */
    private function loadAttributes(array $attributeCodes)
    {
        $normalizedAttributes = $this->loadJsonFileAsArray('attributes/attributes.json');

        $attributeGroup = $this->attributeGroupBuilder->build(['code' => 'other']);
        $this->attributeGroupRepository->save($attributeGroup);

        $attributes = [];
        foreach ($attributeCodes as $attributeCode) {
            $attribute = $this->attributeBuilder->build($normalizedAttributes[$attributeCode]);
            $this->attributeRepository->save($attribute);

            if ('pim_catalog_simpleselect' === $attribute->getType() ||
                'pim_catalog_multiselect' === $attribute->getType()) {
                $this->loadAttributeOptions($attributeCode);
            }
            $attributes[] = $attribute;
        }

        return $attributes;
    }

    private function loadAttributeOptions(string $attributeCode): void
    {
        $flatOptions = $this->loadJsonFileAsArray(sprintf('options/%s-options.json', $attributeCode));
        foreach ($flatOptions as $flatOption) {
            $option = $this->optionBuilder->build($flatOption, false);
            $this->attributeOptionRepository->save($option);
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

        $attributes = $this->loadAttributes($normalizedFamily['attributes']);

        $family = $this->familyBuilder->build($normalizedFamily);
        $this->familyRepository->save($family);

        foreach ($attributes as $attribute) {
            $attribute->addFamily($family);
        }
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
        foreach ($normalizedProduct['values'] as $attrCode => $value) {
            $product->addValue(ScalarValue::value($attrCode, $value[0]['data']));
        }

        if (isset($normalizedProduct['id'])) {
            $product->setId(intval($normalizedProduct['id']));
        }

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
        foreach ($normalizedProduct['values'] as $attrCode => $value) {
            $product->addValue(ScalarValue::value($attrCode, $value[0]['data']));
        }
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
