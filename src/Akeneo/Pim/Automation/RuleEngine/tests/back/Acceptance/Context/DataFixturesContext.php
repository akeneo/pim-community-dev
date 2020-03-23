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

namespace Akeneo\Test\Pim\Automation\RuleEngine\Acceptance\Context;

use Akeneo\Channel\Component\Model\Currency;
use Akeneo\Pim\Enrichment\Component\Category\Model\Category;
use Akeneo\Pim\Enrichment\Component\Product\Builder\ProductBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Factory\WriteValueCollectionFactory;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModel;
use Akeneo\Pim\Enrichment\Component\Product\Value\ScalarValue;
use Akeneo\Pim\Structure\Component\Factory\FamilyFactory;
use Akeneo\Pim\Structure\Component\Model\Attribute;
use Akeneo\ReferenceEntity\Common\Fake\InMemoryFindRecordDetails;
use Akeneo\ReferenceEntity\Common\Fake\InMemoryRecordRepository;
use Akeneo\ReferenceEntity\Common\Fake\InMemoryReferenceEntityRepository;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Image;
use Akeneo\ReferenceEntity\Domain\Model\LabelCollection;
use Akeneo\ReferenceEntity\Domain\Model\Record\Record;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\ChannelReference;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\LocaleReference;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\TextData;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\Value;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\ValueCollection;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\AttributeAsLabelReference;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntity;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Record\RecordDetails;
use Akeneo\Test\Acceptance\Attribute\InMemoryAttributeRepository;
use Akeneo\Test\Acceptance\AttributeGroup\InMemoryAttributeGroupRepository;
use Akeneo\Test\Acceptance\AttributeOption\InMemoryAttributeOptionRepository;
use Akeneo\Test\Acceptance\Category\InMemoryCategoryRepository;
use Akeneo\Test\Acceptance\Currency\InMemoryCurrencyRepository;
use Akeneo\Test\Acceptance\Family\InMemoryFamilyRepository;
use Akeneo\Test\Acceptance\Product\InMemoryProductRepository;
use Akeneo\Test\Common\EntityBuilder;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use Webmozart\Assert\Assert;

/**
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
final class DataFixturesContext implements Context
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

    /** @var EntityBuilder */
    private $categoryBuilder;

    /** @var InMemoryCategoryRepository */
    private $categoryRepository;

    /** @var InMemoryAttributeOptionRepository */
    private $attributeOptionRepository;

    /** @var InMemoryCurrencyRepository */
    private $currencyRepository;

    /** @var ObjectUpdaterInterface */
    private $productUpdater;

    /** EntityBuilder */
    private $optionBuilder;

    /** @var WriteValueCollectionFactory */
    private $valueCollectionFactory;

    /** @var InMemoryReferenceEntityRepository */
    private $referenceEntityRepository;

    /** @var InMemoryRecordRepository */
    private $recordRepository;

    /** @var InMemoryFindRecordDetails */
    private $findRecordDetails;

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
        EntityBuilder $categoryBuilder,
        InMemoryCategoryRepository $categoryRepository,
        InMemoryAttributeOptionRepository $attributeOptionRepository,
        InMemoryCurrencyRepository $currencyRepository,
        EntityBuilder $optionBuilder,
        WriteValueCollectionFactory $valueCollectionFactory,
        InMemoryReferenceEntityRepository $referenceEntityRepository,
        InMemoryRecordRepository $recordRepository,
        InMemoryFindRecordDetails $findRecordDetails
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
        $this->categoryBuilder = $categoryBuilder;
        $this->categoryRepository = $categoryRepository;
        $this->attributeOptionRepository = $attributeOptionRepository;
        $this->currencyRepository = $currencyRepository;
        $this->productUpdater = $productUpdater;
        $this->optionBuilder = $optionBuilder;
        $this->valueCollectionFactory = $valueCollectionFactory;
        $this->referenceEntityRepository = $referenceEntityRepository;
        $this->recordRepository = $recordRepository;
        $this->findRecordDetails = $findRecordDetails;
    }

    /**
     * @Given the family ":familyCode"
     */
    public function theFamily(string $familyCode): void
    {
        $this->loadFamily($familyCode);
    }

    /**
     * @Given /the predefined attributes? (.*)/
     */
    public function thePredefinedAttributes(string $attributes): void
    {
        $this->loadAttributes(array_map('strtolower', $this->toArray($attributes)));
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
     * @Given /^the following categories:$/
     */
    public function theFollowingCategories(TableNode $table): void
    {
        foreach ($table->getHash() as $data) {
            $category = new Category();
            $category->setCode($data['code']);
            if (isset($data['parent'])) {
                $parentCategory = $this->categoryRepository->findOneByIdentifier($data['parent']);
                $category->setParent($parentCategory);
            }

            $this->categoryRepository->save($category);
        }
    }

    /**
     * @Given /^some currencies$/
     */
    public function someCurrencies(): void
    {
        $eurCurrency = new Currency();
        $eurCurrency->setCode('EUR');
        $eurCurrency->setActivated(true);
        $this->currencyRepository->save($eurCurrency);

        $dollarCurrency = new Currency();
        $dollarCurrency->setCode('USD');
        $dollarCurrency->setActivated(true);
        $this->currencyRepository->save($dollarCurrency);
    }

    /**
     * @Given the following :code reference entity
     */
    public function theFollowingReferenceEntity(string $code): void
    {
        $referenceEntity = ReferenceEntity::create(
            ReferenceEntityIdentifier::fromString($code),
            [],
            Image::createEmpty()
        );
        $referenceEntity->updateAttributeAsLabelReference(AttributeAsLabelReference::createFromNormalized('label'));
        $this->referenceEntityRepository->create($referenceEntity);
    }

    /**
     * @Given /^the following records?:$/
     */
    public function theFollowingRecords(TableNode $records): void
    {
        foreach ($records as $normalizedRecord) {
            $record = Record::create(
                RecordIdentifier::fromString($normalizedRecord['code']),
                ReferenceEntityIdentifier::fromString($normalizedRecord['ref entity']),
                RecordCode::fromString($normalizedRecord['code']),
                ValueCollection::fromValues([
                    Value::create(
                        AttributeIdentifier::fromString('label'),
                        ChannelReference::noReference(),
                        LocaleReference::createFromNormalized('en_US'),
                        TextData::fromString('us ' . $normalizedRecord['code'])
                    ),
                    Value::create(
                        AttributeIdentifier::fromString('label'),
                        ChannelReference::noReference(),
                        LocaleReference::createFromNormalized('fr_FR'),
                        TextData::fromString('fr ' . $normalizedRecord['code'])
                    ),
                ])
            );
            $this->recordRepository->create($record);

            $recordDetails = new RecordDetails(
                RecordIdentifier::fromString($normalizedRecord['code']),
                ReferenceEntityIdentifier::fromString($normalizedRecord['ref entity']),
                RecordCode::fromString($normalizedRecord['code']),
                LabelCollection::fromArray([
                    'en_US' => 'us ' . $normalizedRecord['code'],
                    'fr_FR' => 'fr ' . $normalizedRecord['code'],
                ]),
                Image::createEmpty(),
                [
                    'label' => [
                        'en_US' => 'us ' . $normalizedRecord['code'],
                        'fr_FR' => 'fr ' . $normalizedRecord['code'],
                    ],
                ],
                true
            );
            $this->findRecordDetails->save($recordDetails);
        }
    }

    /**
     * Loads attributes according to a provided list of attribute codes and a default attribute group.
     * Fixture content is in a file in "Resources/config/fixtures/attributes/".
     */
    private function loadAttributes(array $attributeCodes): array
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
                $this->loadAttributeOptions($attribute, $attributeCode);
            }
            $this->attributeRepository->save($attribute);
            $attributes[] = $attribute;
        }

        return $attributes;
    }

    private function loadAttributeOptions(Attribute $attribute, string $attributeCode): void
    {
        $flatOptions = $this->loadJsonFileAsArray(sprintf('options/%s-options.json', $attributeCode));
        foreach ($flatOptions as $flatOption) {
            $option = $this->optionBuilder->build($flatOption, false);
            $this->attributeOptionRepository->save($option);
            $attribute->addOption($option);
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

        $valueCollection = $this->valueCollectionFactory->createFromStorageFormat($normalizedProduct['values']);
        $product->setValues($valueCollection);

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
