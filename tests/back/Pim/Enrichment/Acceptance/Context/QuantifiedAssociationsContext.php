<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Acceptance\Context;

use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModel;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Standard\ProductNormalizer;
use Akeneo\Pim\Structure\Component\Model\AssociationType;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\Family;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyVariant;
use Akeneo\Pim\Structure\Component\Model\FamilyVariantInterface;
use Akeneo\Pim\Structure\Component\Model\VariantAttributeSet;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Test\Acceptance\AssociationType\InMemoryAssociationTypeRepository;
use Akeneo\Test\Acceptance\Product\InMemoryProductRepository;
use Akeneo\Test\Acceptance\ProductModel\InMemoryProductModelRepository;
use Akeneo\Test\Common\Structure\Attribute;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Behat\Behat\Context\Context;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Webmozart\Assert\Assert;

final class QuantifiedAssociationsContext implements Context
{
    /** @var Product|null */
    private $product;

    /** @var ProductModel|null */
    private $productModel;

    /** @var FamilyInterface|null */
    private $family;

    /** @var FamilyVariantInterface|null */
    private $familyVariant;

    /** @var AttributeInterface|null */
    private $attribute;

    /** @var \Exception|null */
    private $exception;

    /* --- */

    /** @var ValidatorInterface */
    private $validator;

    /** @var ObjectUpdaterInterface */
    private $productUpdater;

    /** @var ObjectUpdaterInterface */
    private $productModelUpdater;

    /** @var ProductNormalizer */
    private $standardProductNormalizer;

    /** @var InMemoryProductRepository */
    private $productRepository;

    /** @var InMemoryAssociationTypeRepository */
    private $associationTypeRepository;

    /** @var InMemoryProductModelRepository */
    private $productModelRepository;

    public function __construct(
        ValidatorInterface $validator,
        ObjectUpdaterInterface $productUpdater,
        ObjectUpdaterInterface $productModelUpdater,
        ProductNormalizer $standardProductNormalizer,
        InMemoryProductRepository $productRepository,
        InMemoryProductModelRepository $productModelRepository,
        InMemoryAssociationTypeRepository $associationTypeRepository
    ) {
        $this->validator = $validator;
        $this->productUpdater = $productUpdater;
        $this->productModelUpdater = $productModelUpdater;
        $this->standardProductNormalizer = $standardProductNormalizer;
        $this->productRepository = $productRepository;
        $this->productModelRepository = $productModelRepository;
        $this->associationTypeRepository = $associationTypeRepository;
    }

    private function createProduct(array $fields): Product
    {
        $product = new Product();
        $this->updateProduct($product, $fields);

        return $product;
    }

    private function createProductVariant(array $fields): Product
    {
        $product = new Product();
        $this->updateProduct($product, $fields);

        $product->setFamily($this->getFamily());
        $product->setFamilyVariant($this->getFamilyVariant());

        return $product;
    }

    private function updateProduct(Product $product, array $fields): void
    {
        $this->productUpdater->update($product, $fields);
    }

    private function validateEntityWithValues(EntityWithValuesInterface $entity): ConstraintViolationListInterface
    {
        return $this->validator->validate($entity);
    }

    private function createProductModel(array $fields): ProductModel
    {
        $productModel = new ProductModel();
        $this->updateProductModel($productModel, $fields);

        $productModel->setFamilyVariant($this->getFamilyVariant());

        return $productModel;
    }

    private function updateProductModel(ProductModel $productModel, array $fields): void
    {
        $this->productModelUpdater->update($productModel, $fields);
    }

    private function getAttribute(): AttributeInterface
    {
        if (null === $this->attribute) {
            $this->attribute = (new Attribute\Builder())->aIdentifier()
                ->withCode('sku')
                ->build();
        }

        return $this->attribute;
    }

    private function getFamily(): FamilyInterface
    {
        if (null === $this->family) {
            $this->family = new Family();
            $this->family->setCode('furniture');
            $this->family->addAttribute($this->getAttribute());
        }

        return $this->family;
    }

    private function getFamilyVariant(): FamilyVariantInterface
    {
        if (null === $this->familyVariant) {
            $this->familyVariant = new FamilyVariant();
            $this->familyVariant->setFamily($this->getFamily());

            $variantAttributeSet = new VariantAttributeSet();
            $variantAttributeSet->setLevel(1);
            $variantAttributeSet->addAttribute($this->getAttribute());
            $this->familyVariant->addVariantAttributeSet($variantAttributeSet);
        }

        return $this->familyVariant;
    }

    private function createAndPersistProductWithIdentifier(string $identifier): void
    {
        $this->productRepository->save($this->createProduct([
            'values' => [
                'sku' => [
                    [
                        'scope' => null,
                        'locale' => null,
                        'data' => $identifier,
                    ],
                ],
            ],
        ]));
    }

    private function createAndPersistProductModelWithCode(string $code): void
    {
        $this->productModelRepository->save($this->createProductModel([
            'code' => $code,
        ]));
    }

    private function createAndPersistQuantifiedAssociationType(string $code): void
    {
        $associationType = new AssociationType();
        $associationType->setCode($code);
        $associationType->setIsQuantified(true);

        $this->associationTypeRepository->save($associationType);
    }

    private function createAndPersistNormalAssociationType(string $code): void
    {
        $associationType = new AssociationType();
        $associationType->setCode($code);
        $associationType->setIsQuantified(false);

        $this->associationTypeRepository->save($associationType);
    }

    /**
     * @Given /^a product without quantified associations$/
     */
    public function aProductWithoutQuantifiedAssociations()
    {
        $this->product = $this->createProduct([
            'values' => [
                'sku' => [
                    [
                        'scope' => null,
                        'locale' => null,
                        'data' => 'yellow_chair',
                    ],
                ],
            ],
        ]);
    }

    /**
     * @Given /^a product without associations$/
     */
    public function aProductWithoutAssociations()
    {
        $this->product = $this->createProduct([
            'values' => [
                'sku' => [
                    [
                        'scope' => null,
                        'locale' => null,
                        'data' => 'yellow_chair',
                    ],
                ],
            ],
        ]);
    }

    /**
     * @Given /^a product variant without quantified associations$/
     */
    public function aProductVariantWithoutQuantifiedAssociations()
    {
        $this->product = $this->createProductVariant([
            'values' => [
                'sku' => [
                    [
                        'scope' => null,
                        'locale' => null,
                        'data' => 'yellow_chair',
                    ],
                ],
            ],
        ]);
    }

    /**
     * @Then /^this product should be associated to this other product$/
     */
    public function thisProductShouldBeAssociatedToThisOtherProduct()
    {
        $actualQuantifiedAssociations = $this->product->normalizeQuantifiedAssociations();
        $expectedQuantifiedAssociations = [
            'PACK' => [
                'products' => [
                    ['identifier' => 'accessory', 'quantity' => 42],
                ],
                'product_models' => [],
            ],
        ];

        Assert::same($actualQuantifiedAssociations, $expectedQuantifiedAssociations);
    }

    /**
     * @When /^I associate a product to this product with a quantity$/
     */
    public function iAssociateAProductToThisProductWithAQuantity()
    {
        $this->createAndPersistProductWithIdentifier('accessory');

        $fields = [
            'quantified_associations' => [
                'PACK' => [
                    'products' => [
                        ['identifier' => 'accessory', 'quantity' => 42],
                    ],
                    'product_models' => [],
                ],
            ],
        ];

        $this->updateProduct($this->product, $fields);
        Assert::count($this->validateEntityWithValues($this->product), 0);
    }

    /**
     * @Given /^a product model without quantified associations$/
     */
    public function aProductModelWithoutQuantifiedAssociations()
    {
        $this->productModel = $this->createProductModel([
            'code' => 'standard_chair',
        ]);
    }

    /**
     * @Given /^a product model without associations$/
     */
    public function aProductModelWithoutAssociations()
    {
        $this->productModel = $this->createProductModel([
            'code' => 'standard_chair',
        ]);
    }

    /**
     * @When /^I associate a product to this product model with a quantity$/
     */
    public function iAssociateAProductToThisProductModelWithAQuantity()
    {
        $this->createAndPersistProductWithIdentifier('accessory');

        $fields = [
            'quantified_associations' => [
                'PACK' => [
                    'products' => [
                        ['identifier' => 'accessory', 'quantity' => 42],
                    ],
                    'product_models' => [],
                ],
            ],
        ];

        $this->updateProductModel($this->productModel, $fields);
        Assert::count($this->validateEntityWithValues($this->productModel), 0);
    }

    /**
     * @Then /^this product model should be associated to this other product$/
     */
    public function thisProductModelShouldBeAssociatedToThisOtherProduct()
    {
        $actualQuantifiedAssociations = $this->productModel->normalizeQuantifiedAssociations();
        $expectedQuantifiedAssociations = [
            'PACK' => [
                'products' => [
                    ['identifier' => 'accessory', 'quantity' => 42],
                ],
                'product_models' => [],
            ],
        ];

        Assert::same($actualQuantifiedAssociations, $expectedQuantifiedAssociations);
    }

    /**
     * @Given /^this product has a parent with a quantified associations$/
     */
    public function thisProductHasAParentWithAQuantifiedAssociations()
    {
        $productModel = $this->createProductModel([
            'code' => 'standard_chair',
            'quantified_associations' => [
                'PACK' => [
                    'products' => [
                        ['identifier' => 'accessory', 'quantity' => 66],
                        ['identifier' => 'something_else', 'quantity' => 2],
                    ],
                    'product_models' => [],
                ],
            ],
        ]);

        $this->product->setParent($productModel);
    }

    /**
     * @When /^I add the same quantified association with a different quantity$/
     */
    public function iAddTheSameQuantifiedAssociationWithADifferentQuantity()
    {
        $this->createAndPersistProductWithIdentifier('accessory');

        $fields = [
            'quantified_associations' => [
                'PACK' => [
                    'products' => [
                        ['identifier' => 'accessory', 'quantity' => 42],
                    ],
                    'product_models' => [],
                ],
            ],
        ];

        $this->updateProduct($this->product, $fields);
        Assert::count($this->validateEntityWithValues($this->product), 0);
    }

    /**
     * @Then /^this product should have this quantified association and all the other parent quantified associations$/
     */
    public function thisProductShouldHaveThisQuantifiedAssociationAndAllTheOtherParentQuantifiedAssociations()
    {
        $normalizedProduct = $this->standardProductNormalizer->normalize($this->product, 'standard');

        $actualQuantifiedAssociations = $normalizedProduct['quantified_associations'];
        $expectedQuantifiedAssociations = [
            'PACK' => [
                'products' => [
                    ['identifier' => 'accessory', 'quantity' => 42],
                    ['identifier' => 'something_else', 'quantity' => 2],
                ],
                'product_models' => [],
            ],
        ];

        Assert::same($actualQuantifiedAssociations, $expectedQuantifiedAssociations);
    }

    /**
     * @When /^I associate a product model to this product model with a quantity$/
     */
    public function iAssociateAProductModelToThisProductModelWithAQuantity()
    {
        $this->createAndPersistProductModelWithCode('accessory');

        $fields = [
            'quantified_associations' => [
                'PACK' => [
                    'products' => [],
                    'product_models' => [
                        ['identifier' => 'accessory', 'quantity' => 42],
                    ],
                ],
            ],
        ];

        $this->updateProductModel($this->productModel, $fields);
        Assert::count($this->validateEntityWithValues($this->productModel), 0);
    }

    /**
     * @Then /^this product model should be associated to this other product model$/
     */
    public function thisProductModelShouldBeAssociatedToThisOtherProductModel()
    {
        $actualQuantifiedAssociations = $this->productModel->normalizeQuantifiedAssociations();
        $expectedQuantifiedAssociations = [
            'PACK' => [
                'products' => [],
                'product_models' => [
                    ['identifier' => 'accessory', 'quantity' => 42],
                ],
            ],
        ];

        Assert::same($actualQuantifiedAssociations, $expectedQuantifiedAssociations);
    }

    /**
     * @Given /^a quantified association type "([^"]*)"$/
     */
    public function aQuantifiedAssociationType($code)
    {
        $this->createAndPersistQuantifiedAssociationType($code);
    }

    /**
     * @Then /^there is the validation error "(.*)"$/
     */
    public function thereIsTheValidationError($message)
    {
        /** @var ConstraintViolationListInterface $violations */
        $violations = $this->validator->validate($this->product);

        $violationsMessages = [];

        /** @var ConstraintViolation $violation */
        foreach ($violations as $violation) {
            $violationsMessages[] = $violation->getMessageTemplate();
        }

        Assert::true(in_array($message, $violationsMessages), sprintf(
            'The validation error "%s" was not found, got "%s"',
            $message,
            implode(',', $violationsMessages)
        ));
    }

    /**
     * @When /^a product is associated with a quantity for an association type that does not exist$/
     */
    public function aProductIsAssociatedWithAQuantityForAnAssociationTypeThatDoesNotExist(): void
    {
        $this->product = $this->createProduct([
            'values' => [
                'sku' => [
                    [
                        'scope' => null,
                        'locale' => null,
                        'data' => 'yellow_chair',
                    ],
                ],
            ],
            'quantified_associations' => [
                'INVALID_ASSOCIATION_TYPE' => [
                    'products' => [],
                    'product_models' => [],
                ],
            ],
        ]);
    }

    /**
     * @When /^a product is associated with a quantity for an association type that is not quantified$/
     */
    public function aProductIsAssociatedWithAQuantityForAnAssociationTypeThatIsNotQuantified()
    {
        $this->createAndPersistNormalAssociationType('XSELL');

        $this->product = $this->createProduct([
            'values' => [
                'sku' => [
                    [
                        'scope' => null,
                        'locale' => null,
                        'data' => 'yellow_chair',
                    ],
                ],
            ],
            'quantified_associations' => [
                'XSELL' => [
                    'products' => [],
                    'product_models' => [],
                ],
            ],
        ]);
    }

    /**
     * @When /^I associate a product to this product with the quantity "([^"]*)"$/
     */
    public function iAssociateAProductToThisProductWithTheQuantity($quantity)
    {
        $this->createAndPersistProductWithIdentifier('accessory');
        $this->createAndPersistQuantifiedAssociationType('PACK');

        $fields = [
            'quantified_associations' => [
                'PACK' => [
                    'products' => [
                        ['identifier' => 'accessory', 'quantity' => (int)$quantity],
                    ],
                    'product_models' => [],
                ],
            ],
        ];

        $this->updateProduct($this->product, $fields);
    }

    /**
     * @When /^I associate a nonexistent product to this product with a quantity$/
     */
    public function iAssociateANonExistentProductToThisProductWithAQuantity()
    {
        $this->createAndPersistQuantifiedAssociationType('PACK');
        $fields = [
            'quantified_associations' => [
                'PACK' => [
                    'products' => [
                        ['identifier' => 'accessory', 'quantity' => 1],
                    ],
                    'product_models' => [],
                ],
            ],
        ];

        $this->updateProduct($this->product, $fields);
    }

    /**
     * @When /^I associate a nonexistent product model to this product with a quantity$/
     */
    public function iAssociateANonExistentProductModelToThisProductWithAQuantity()
    {
        $this->createAndPersistQuantifiedAssociationType('PACK');
        $fields = [
            'quantified_associations' => [
                'PACK' => [
                    'products' => [],
                    'product_models' => [
                        ['identifier' => 'accessory', 'quantity' => 42],
                    ],
                ],
            ],
        ];

        $this->updateProduct($this->product, $fields);
    }

    /**
     * @When /^I associate "([^"]*)" products and "([^"]*)" product models with a quantity to this product$/
     */
    public function iAssociateProductsWithAQuantityToThisProduct(
        string $numberOfProductAssociation,
        string $numberOfProductModelAssociation
    ) {
        $productAssociations = [];
        $productModelAssociations = [];
        for ($i = 0; $i < intval($numberOfProductAssociation); $i++) {
            $productIdentifier = "product-$i";
            $this->createAndPersistProductWithIdentifier($productIdentifier);

            $productAssociations[] = ['identifier' => $productIdentifier, 'quantity' => 42];
        }

        for ($i = 0; $i < intval($numberOfProductModelAssociation); $i++) {
            $productModelCode = "product-model-$i";
            $this->createAndPersistProductModelWithCode($productModelCode);

            $productModelAssociations[] = ['identifier' => $productModelCode, 'quantity' => 42];
        }

        $this->createAndPersistQuantifiedAssociationType('PACK');
        $fields = [
            'quantified_associations' => [
                'PACK' => [
                    'products' => $productAssociations,
                    'product_models' => $productModelAssociations,
                ],
            ],
        ];

        $this->updateProduct($this->product, $fields);
    }

    /**
     * @When /^a product is associated with invalid quantified link type$/
     */
    public function aProductWithInvalidQuantifiedLinkType()
    {
        $this->createAndPersistQuantifiedAssociationType('PACK');

        $this->product = $this->createProduct([
            'values' => [
                'sku' => [
                    [
                        'scope' => null,
                        'locale' => null,
                        'data' => 'yellow_chair',
                    ],
                ],
            ],
            'quantified_associations' => [
                'PACK' => [
                    'products' => [],
                    'product_drafts' => [],
                    'product_models' => [],
                ],
            ],
        ]);
    }

    /**
     * @Then /^the product is valid$/
     */
    public function theProductIsValid()
    {
        $violations = $this->validator->validate($this->product);
        Assert::count($violations, 0);
    }

    /**
     * @When /^a product model is associated with an invalid quantified association$/
     */
    public function aProductModelWithAnInvalidQuantifiedAssociation()
    {
        $this->productModel = $this->createProductModel([
            'code' => 'standard_chair',
            'quantified_associations' => [
                'INVALID_ASSOCIATION_TYPE' => [
                    'products' => [
                        ['identifier' => 'accessory', 'quantity' => -1],
                        ['identifier' => 'something_else', 'quantity' => 10000],
                    ],
                    'product_models' => [],
                    'product_drafts' => [],
                ],
            ],
        ]);
    }

    /**
     * @Then /^there is at least a validation error on this product model$/
     */
    public function thereIsAtLeastAValidationErrorOnThisProductModel()
    {
        $violations = $this->validator->validate($this->productModel);
        Assert::greaterThan(count($violations), 0);
    }

    private function assertEntityHasValidationError($entity, string $message, string $propertyPath)
    {
        /** @var ConstraintViolationListInterface $violations */
        $violations = $this->validator->validate($entity);

        /** @var ConstraintViolation $violation */
        foreach ($violations as $violation) {
            if ($violation->getMessageTemplate() === $message && $violation->getPropertyPath() === $propertyPath) {
                return;
            }
        }

        throw new \InvalidArgumentException(sprintf(
            'The expected validation error was not found, got %d errors: "%s"',
            count($violations),
            implode(',', iterator_to_array($violations))
        ));
    }

    /**
     * @Then /^this product has a validation error about association type does not exist$/
     */
    public function thisProductHasAValidationErrorAboutAssociationTypeDoesNotExist()
    {
        $this->assertEntityHasValidationError(
            $this->product,
            'pim_catalog.constraint.quantified_associations.association_type_does_not_exist',
            'quantifiedAssociations.INVALID_ASSOCIATION_TYPE'
        );
    }

    /**
     * @Then /^this product has a validation error about association type is not quantified$/
     */
    public function thisProductHasAValidationErrorAboutAssociationTypeIsNotQuantified()
    {
        $this->assertEntityHasValidationError(
            $this->product,
            'pim_catalog.constraint.quantified_associations.association_type_is_not_quantified',
            'quantifiedAssociations.XSELL'
        );
    }

    /**
     * @Then /^this product has a validation error about invalid quantity$/
     */
    public function thisProductHasAValidationErrorAboutInvalidQuantity()
    {
        $this->assertEntityHasValidationError(
            $this->product,
            'pim_catalog.constraint.quantified_associations.invalid_quantity',
            'quantifiedAssociations.PACK.products[0].quantity'
        );
    }

    /**
     * @Then /^this product has a validation error about product do not exist$/
     */
    public function thisProductHasAValidationErrorAboutProductDoNotExist()
    {
        $this->assertEntityHasValidationError(
            $this->product,
            'pim_catalog.constraint.quantified_associations.products_do_not_exist',
            'quantifiedAssociations.PACK.products'
        );
    }

    /**
     * @Then /^this product has a validation about product models do not exist$/
     */
    public function thisProductHasAValidationAboutProductModelsDoNotExist()
    {
        $this->assertEntityHasValidationError(
            $this->product,
            'pim_catalog.constraint.quantified_associations.product_models_do_not_exist',
            'quantifiedAssociations.PACK.product_models'
        );
    }

    /**
     * @Then /^this product has a validation about maximum number of associations$/
     */
    public function thisProductHasAValidationAboutMaximumNumberOfAssociations()
    {
        $this->assertEntityHasValidationError(
            $this->product,
            'pim_catalog.constraint.quantified_associations.max_associations',
            'quantifiedAssociations.PACK'
        );
    }

    /**
     * @Then /^this product has a validation error about unexpected link type$/
     */
    public function thisProductHasAValidationErrorAboutUnexpectedLinkType()
    {
        $this->assertEntityHasValidationError(
            $this->product,
            'pim_catalog.constraint.quantified_associations.unexpected_link_type',
            'quantifiedAssociations.PACK'
        );
    }

    /**
     * @When /^I add an association without quantity to this product using a quantified association type$/
     */
    public function iAddAnAssociationWithoutQuantityToThisProductUsingAQuantifiedAssociationType()
    {
        $this->createAndPersistQuantifiedAssociationType('PACK');
        $this->createAndPersistProductWithIdentifier('accessory');

        $fields = [
            'associations' => [
                'PACK' => [
                    'products' => ['accessory'],
                ],
            ],
        ];

        try {
            $this->updateProduct($this->product, $fields);
        } catch (\Exception $exception) {
            $this->exception = $exception;
        }
    }

    /**
     * @Then /^this product has a validation error about association type should not be quantified$/
     *
     * The AssociationFieldSetter throw an exception when the association type does not exist.
     * It think the association type does not exists because the sql query exclude quantified association types by default.
     * InvalidPropertyException SHOULD be used for structure, not data.
     * This SHOULD NOT happen in the updater but in the validator.
     * To be future-proof, this test accept one of those two errors.
     */
    public function thisProductHasAValidationErrorAboutAssociationTypeShouldNotBeQuantified()
    {
        if ($this->exception) {
            Assert::isInstanceOf($this->exception, InvalidPropertyException::class);
            Assert::same($this->exception->getMessage(), 'Property "associations" expects a valid association type code. The association type does not exist or is quantified, "PACK" given.');
        } else {
            $this->assertEntityHasValidationError(
                $this->product,
                'pim_catalog.constraint.quantified_associations.association_type_should_not_be_quantified',
                'associations[0]'
            );
        }
    }

    /**
     * @When /^I add an association without quantity to this product model using a quantified association type$/
     */
    public function iAddAnAssociationWithoutQuantityToThisProductModelUsingAQuantifiedAssociationType()
    {
        $this->createAndPersistQuantifiedAssociationType('PACK');
        $this->createAndPersistProductWithIdentifier('accessory');

        $fields = [
            'associations' => [
                'PACK' => [
                    'products' => ['accessory'],
                ],
            ],
        ];

        try {
            $this->updateProductModel($this->productModel, $fields);
        } catch (\Exception $exception) {
            $this->exception = $exception;
        }
    }

    /**
     * @Then /^this product model has a validation error about association type should not be quantified$/
     *
     * The AssociationFieldSetter throw an exception when the association type does not exist.
     * It think the association type does not exists because the sql query exclude quantified association types by default.
     * InvalidPropertyException SHOULD be used for structure, not data.
     * This SHOULD NOT happen in the updater but in the validator.
     * To be future-proof, this test accept one of those two errors.
     */
    public function thisProductModelHasAValidationErrorAboutAssociationTypeShouldNotBeQuantified()
    {
        if ($this->exception) {
            Assert::isInstanceOf($this->exception, InvalidPropertyException::class);
            Assert::same($this->exception->getMessage(), 'Property "associations" expects a valid association type code. The association type does not exist or is quantified, "PACK" given.');
        } else {
            $this->assertEntityHasValidationError(
                $this->productModel,
                'pim_catalog.constraint.quantified_associations.association_type_should_not_be_quantified',
                'associations[0]'
            );
        }
    }
}
