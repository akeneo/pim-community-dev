<?php
declare(strict_types=1);

namespace Pim\Behat\Context\Storage;

use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\AttributeColumnInfoExtractor;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductModelRepositoryInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Repository\FamilyVariantRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Behat\Gherkin\Node\TableNode;
use Behat\MinkExtension\Context\RawMinkContext;
use PHPUnit\Framework\Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ProductModelStorage extends RawMinkContext
{
    /** @var array */
    private $productModelFields = ['code', 'parent', 'categories', 'family_variant'];

    /** @var AttributeColumnInfoExtractor */
    private $attributeColumnInfoExtractor;

    /** @var ProductModelRepositoryInterface */
    private $productModelRepository;

    /** @var FamilyVariantRepositoryInterface */
    private $familyVariantRepository;

    /** @var SimpleFactoryInterface */
    private $productModelFactory;

    /** @var ObjectUpdaterInterface */
    private $productModelUpdater;

    /** @var ValidatorInterface */
    private $productModelValidator;

    /** @var SaverInterface */
    private $productModelSaver;

    /**
     * @param AttributeColumnInfoExtractor     $attributeColumnInfoExtractor
     * @param ProductModelRepositoryInterface  $productModelRepository
     * @param FamilyVariantRepositoryInterface $familyVariantRepository
     * @param SimpleFactoryInterface           $productModelFactory
     * @param ObjectUpdaterInterface           $productModelUpdater
     * @param ValidatorInterface               $productModelValidator
     * @param SaverInterface                   $productModelSaver
     */
    public function __construct(
        AttributeColumnInfoExtractor $attributeColumnInfoExtractor,
        ProductModelRepositoryInterface $productModelRepository,
        FamilyVariantRepositoryInterface $familyVariantRepository,
        SimpleFactoryInterface $productModelFactory,
        ObjectUpdaterInterface $productModelUpdater,
        ValidatorInterface $productModelValidator,
        SaverInterface $productModelSaver
    ) {
        $this->attributeColumnInfoExtractor = $attributeColumnInfoExtractor;
        $this->productModelRepository = $productModelRepository;
        $this->familyVariantRepository = $familyVariantRepository;
        $this->productModelFactory = $productModelFactory;
        $this->productModelUpdater = $productModelUpdater;
        $this->productModelValidator = $productModelValidator;
        $this->productModelSaver = $productModelSaver;
    }

    /**
     * @Then /^there should be the following (?:|root product model|product model):$/
     */
    public function theProductShouldHaveTheFollowingValues(TableNode $properties)
    {
        foreach ($properties->getHash() as $rawProductModel) {
            $productModel = $this->productModelRepository->findOneByIdentifier($rawProductModel['code']);

            if (null === $productModel) {
                throw new \Exception(
                    sprintf('The model with the code "%s" does not exist', $rawProductModel['code'])
                );
            }

            foreach ($rawProductModel as $propertyName => $value) {
                if (in_array($propertyName, $this->productModelFields)) {
                    $this->checkProductModelField($productModel, $propertyName, $value);
                } else {
                    $this->checkProductModelValue($productModel, $propertyName, $value);
                }
            }
        }
    }

    /**
     * @Given the product model :identifier should not have the following values :attributesCodes
     */
    public function theProductShouldNotHaveTheFollowingValues($code, $attributesCodes)
    {
        $attributesCodes = explode(',', $attributesCodes);
        $attributesCodes = array_map('trim', $attributesCodes);

        /** @var ProductModelInterface $productModel */
        $productModel = $this->productModelRepository->findOneByIdentifier($code);

        if (null === $productModel) {
            throw new \Exception(
                sprintf('The model with the identifier "%s" does not exist', $code)
            );
        }
        foreach ($attributesCodes as $propertyName) {
            $infos = $this->attributeColumnInfoExtractor->extractColumnInfo($propertyName);
            /** @var AttributeInterface $attribute */
            $attribute = $infos['attribute'];
            $productValue = $productModel->getValuesForVariation()->getByCodes(
                $attribute->getCode(),
                $infos['locale_code'],
                $infos['scope_code']
            );

            if (null !== $productValue) {
                throw new \Exception(
                    sprintf('The value "%s" for product model "%s" exists', $attribute->getCode(), $code)
                );
            }
        }
    }

    /**
     * @Given the following root product model :productModel with the variant family :variantFamily
     */
    public function createRootProductModel(string $productModelCode, string $variantFamilyCode)
    {
        $productModel = $this->productModelFactory->create();
        $this->productModelUpdater->update($productModel, [
            'code' => $productModelCode,
            'parent' => '',
            'family_variant' => $variantFamilyCode,
        ]);

        $this->productModelValidator->validate($productModel);
        $this->productModelSaver->save($productModel);
    }

    /**
     * @Given the following sub product model :productModel with :parent as parent
     */
    public function createSubProductModel(string $productModelCode, string $parentCode)
    {
        /** @var ProductModelInterface $parentProductModel */
        $parentProductModel = $this->productModelRepository->findOneByIdentifier($parentCode);
        $productModel = $this->productModelFactory->create();
        $this->productModelUpdater->update($productModel, [
            'code' => $productModelCode,
            'parent' => $parentCode,
            'family_variant' => $parentProductModel->getFamilyVariant()->getCode(),
        ]);

        $this->productModelValidator->validate($productModel);
        $this->productModelSaver->save($productModel);
    }

    /**
     * @param string $productModelCode
     *
     * @Then product model :productModelCode should not have any children
     */
    public function productModelShouldNotHaveAnyChildren(string $productModelCode): void
    {
        $parentProductModel = $this->productModelRepository->findOneByIdentifier($productModelCode);

        $children = $parentProductModel->getProducts();

        Assert::assertTrue($children->isEmpty());
    }

    /**
     * @param ProductModelInterface $productModel
     * @param string                $propertyName
     * @param mixed                 $value
     *
     * @throws \PHPUnit_Framework_Exception
     */
    private function checkProductModelField(ProductModelInterface $productModel, string $propertyName, $value): void
    {
        switch ($propertyName) {
            case 'parent':
                $actualParent = $productModel->getParent();
                $expectedParent = $this->productModelRepository->findOneByIdentifier($value);

                Assert::assertSame($actualParent, $expectedParent);
                break;
            case 'family_variant':
                $actualFamilyVariant = $productModel->getFamilyVariant();
                $expectedFamilyVariant = $this->familyVariantRepository->findOneByIdentifier($value);

                Assert::assertSame($actualFamilyVariant, $expectedFamilyVariant);
                break;
            case 'categories':
                $actualCategoryCodes = $productModel->getCategoryCodes();
                $expectedCategoryCodes = explode(',', $value);

                Assert::assertSame($actualCategoryCodes, $expectedCategoryCodes);
                break;
        }
    }

    /**
     * @param ProductModelInterface $productModel
     * @param string                $propertyName
     * @param mixed                 $value
     *
     * @throws \PHPUnit_Framework_Exception
     */
    private function checkProductModelValue(ProductModelInterface $productModel, string $propertyName, $value): void
    {
        $infos = $this->attributeColumnInfoExtractor->extractColumnInfo($propertyName);

        $attribute = $infos['attribute'];
        $priceCurrency = isset($infos['price_currency']) ? $infos['price_currency'] : null;
        $productValue = $productModel->getValue(
            $attribute->getCode(),
            $infos['locale_code'],
            $infos['scope_code']
        );

        if ('' === $value) {
            Assert::assertEmpty((string)$productValue);
        } elseif ('prices' === $attribute->getBackendType() && null !== $priceCurrency) {
            // $priceCurrency can be null if we want to test all the currencies at the same time
            // in this case, it's a simple string comparison
            // example: 180.00 EUR, 220.00 USD

            $price = $productValue->getPrice($priceCurrency);
            Assert::assertEquals($value, $price->getData());
        } elseif ('date' === $attribute->getBackendType()) {
            Assert::assertEquals($value, $productValue->getData()->format('Y-m-d'));
        } else {
            Assert::assertEquals($value, (string)$productValue);
        }
    }
}
