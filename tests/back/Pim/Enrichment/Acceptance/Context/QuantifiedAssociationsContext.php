<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Acceptance\Context;

use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModel;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Standard\ProductNormalizer;
use Akeneo\Pim\Enrichment\Product\API\Command\Exception\LegacyViolationsException;
use Akeneo\Pim\Enrichment\Product\API\Command\Exception\ViolationsException;
use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ChangeParent;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\AssociateQuantifiedProductModels;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\AssociateQuantifiedProducts;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\QuantifiedEntity;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\ReplaceAssociatedQuantifiedProductUuids;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\UserIntent;
use Akeneo\Pim\Enrichment\Product\API\Query\GetUserIntentsFromStandardFormat;
use Akeneo\Pim\Enrichment\Product\API\ValueObject\ProductIdentifier;
use Akeneo\Pim\Enrichment\Product\Test\Acceptance\InMemory\InMemoryGetAttributeTypes;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AssociationType;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\Family;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyVariant;
use Akeneo\Pim\Structure\Component\Model\FamilyVariantInterface;
use Akeneo\Pim\Structure\Component\Model\VariantAttributeSet;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Test\Acceptance\AssociationType\InMemoryAssociationTypeRepository;
use Akeneo\Test\Acceptance\Product\InMemoryProductRepository;
use Akeneo\Test\Acceptance\ProductModel\InMemoryProductModelRepository;
use Akeneo\Test\Common\EntityBuilder;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Akeneo\UserManagement\Component\Repository\UserRepositoryInterface;
use Behat\Behat\Context\Context;
use Exception;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Webmozart\Assert\Assert;
use Webmozart\Assert\InvalidArgumentException;

final class QuantifiedAssociationsContext implements Context
{
    private ProductInterface|null $product = null;
    private ProductModel|null $productModel = null;
    private FamilyInterface|null $family = null;
    private FamilyVariantInterface|null $familyVariant = null;
    private AttributeInterface|null $attribute = null;
    private Exception|null $exception = null;

    public function __construct(
        private readonly ValidatorInterface $validator,
        private readonly ObjectUpdaterInterface $productModelUpdater,
        private readonly ProductNormalizer $standardProductNormalizer,
        private readonly InMemoryProductRepository $productRepository,
        private readonly InMemoryProductModelRepository $productModelRepository,
        private readonly InMemoryAssociationTypeRepository $associationTypeRepository,
        private readonly UserRepositoryInterface $userRepository,
        private readonly MessageBusInterface $messageBus,
        private readonly MessageBusInterface $queryMessageBus,
        private readonly AttributeRepositoryInterface $attributeRepository,
        private readonly EntityBuilder $attributeBuilder,
        private readonly InMemoryGetAttributeTypes $getAttributeTypes,
    ) {
    }

    private function updateProduct(Product $product, array $userIntents): void
    {
        Assert::allIsInstanceOf($userIntents, UserIntent::class);
        $command = UpsertProductCommand::createWithIdentifier(
            $this->getUserId('admin'),
            ProductIdentifier::fromIdentifier($product->getIdentifier()),
            $userIntents
        );
        try {
            $this->messageBus->dispatch($command);
        } catch (Exception $e) {
            $this->exception = $e;
        }
    }

    private function createProductModel(array $fields): ProductModel
    {
        $productModel = new ProductModel();
        $this->updateProductModel($productModel, $fields);
        $productModel->setFamilyVariant($this->getFamilyVariant());
        $this->productModelRepository->save($productModel);

        return $productModel;
    }

    private function updateProductModel(ProductModel $productModel, array $fields): void
    {
        $this->productModelUpdater->update($productModel, $fields);
        $this->productModelRepository->save($productModel);
    }

    private function getNameAttribute(): AttributeInterface
    {
        if (null === $this->attribute) {
            $this->attribute = $this->attributeBuilder->build([
                'code' => 'name',
                'group' => 'other',
                'type' => AttributeTypes::TEXT,
            ], true);
            $this->attributeRepository->save($this->attribute);
            $this->getAttributeTypes->saveAttribute('name', AttributeTypes::TEXT);
        }

        return $this->attribute;
    }

    private function getFamily(): FamilyInterface
    {
        if (null === $this->family) {
            $this->family = new Family();
            $this->family->setCode('furniture');
            $this->family->addAttribute($this->getNameAttribute());
            $this->family->addAttribute($this->getIdentifierAttribute());
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
            $variantAttributeSet->addAttribute($this->getNameAttribute());
            $variantAttributeSet->addAttribute($this->getIdentifierAttribute());
            $this->familyVariant->addVariantAttributeSet($variantAttributeSet);
        }

        return $this->familyVariant;
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
        $this->product = $this->createProduct('yellow_chair');
    }

    /**
     * @Given /^a product without associations$/
     */
    public function aProductWithoutAssociations()
    {
        $this->product = $this->createProduct('yellow_chair');
    }

    /**
     * @Given /^a product variant without quantified associations$/
     */
    public function aProductVariantWithoutQuantifiedAssociations()
    {
        $this->product = $this->createProduct('yellow_chair');
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
        $this->createProduct('accessory');

        $this->updateProduct($this->product, [
            new AssociateQuantifiedProducts('PACK', [
                new QuantifiedEntity('accessory', 42)
            ]),
        ]);
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
        $this->createProduct('accessory');

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
        Assert::count($this->validator->validate($this->productModel), 0);
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

        $this->updateProduct($this->product, [
            new ChangeParent($productModel->getCode()),
        ]);
    }

    /**
     * @When /^I add the same quantified association with a different quantity$/
     */
    public function iAddTheSameQuantifiedAssociationWithADifferentQuantity()
    {
        $this->createProduct('accessory');

        $this->updateProduct($this->product, [
            new AssociateQuantifiedProducts('PACK', [
                new QuantifiedEntity('accessory', 42)
            ]),
        ]);
        $this->product = $this->productRepository->findOneByIdentifier($this->product->getIdentifier());
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
                    ['identifier' => 'something_else', 'quantity' => 2],
                    ['identifier' => 'accessory', 'quantity' => 42],
                ],
                'product_models' => [],
            ],
        ];

        Assert::eq($actualQuantifiedAssociations, $expectedQuantifiedAssociations, sprintf(
            "Actual:\n%s\n\nExpected:\n%s",
            \json_encode($actualQuantifiedAssociations),
            \json_encode($expectedQuantifiedAssociations)
        ));
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
        Assert::count($this->validator->validate($this->productModel), 0);
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
        $this->createAndPersistQuantifiedAssociationType('XSELL');
        $this->createProduct('accessory');
        $this->createProduct('yellow_chair', [
            new AssociateQuantifiedProducts('INVALID_ASSOCIATION_TYPE', [
                new QuantifiedEntity('accessory', 69)
            ])
        ]);
    }

    /**
     * @When /^a product is associated with a quantity for an association type that is not quantified$/
     */
    public function aProductIsAssociatedWithAQuantityForAnAssociationTypeThatIsNotQuantified()
    {
        $this->createAndPersistNormalAssociationType('XSELL');
        $this->createProduct('accessory');

        $this->product = $this->createProduct('yellow_chair', [
            new AssociateQuantifiedProducts('XSELL', [
                new QuantifiedEntity('accessory', 42)
            ])
        ]);
    }

    /**
     * @When /^I associate a product to this product with the quantity "([^"]*)"$/
     */
    public function iAssociateAProductToThisProductWithTheQuantity($quantity)
    {
        $this->createAndPersistQuantifiedAssociationType('PACK');
        $this->createProduct('accessory');
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

        $this->updateProductFromStandardFormat($this->product, $fields);
    }

    /**
     * @When /^I associate a nonexistent product to this product with a quantity$/
     */
    public function iAssociateANonExistentProductToThisProductWithAQuantity()
    {
        $this->createAndPersistQuantifiedAssociationType('PACK');

        $this->updateProduct($this->product, [
            new AssociateQuantifiedProducts('PACK', [
                new QuantifiedEntity('accessory', 1)
            ]),
        ]);
    }

    /**
     * @When /^I associate a nonexistent product uuid to this product with a quantity$/
     */
    public function iAssociateANonExistentProductUuidToThisProductWithAQuantity()
    {
        $this->createAndPersistQuantifiedAssociationType('PACK');

        $this->updateProduct($this->product, [
            new ReplaceAssociatedQuantifiedProductUuids('PACK', [
                new QuantifiedEntity('5de1519f-85e8-4da8-9caf-937beeec0517', 1)
            ]),
        ]);
    }

    /**
     * @When /^I associate a nonexistent product model to this product with a quantity$/
     */
    public function iAssociateANonExistentProductModelToThisProductWithAQuantity()
    {
        $this->createAndPersistQuantifiedAssociationType('PACK');

        $this->updateProduct($this->product, [
            new AssociateQuantifiedProductModels('PACK', [
                new QuantifiedEntity('accessory', 42)
            ]),
        ]);
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
            $this->createProduct($productIdentifier);

            $productAssociations[] = new QuantifiedEntity($productIdentifier, 42);
        }

        for ($i = 0; $i < intval($numberOfProductModelAssociation); $i++) {
            $productModelCode = "product-model-$i";
            $this->createAndPersistProductModelWithCode($productModelCode);

            $productModelAssociations[] = new QuantifiedEntity($productModelCode, 42);
        }

        $this->createAndPersistQuantifiedAssociationType('PACK');

        $userIntents = [];
        if (!empty($productAssociations)) {
            $userIntents[] = new AssociateQuantifiedProducts('PACK', $productAssociations);
        }
        if (!empty($productModelAssociations)) {
            $userIntents[] = new AssociateQuantifiedProductModels('PACK', $productModelAssociations);
        }

        $this->updateProduct($this->product, $userIntents);
    }

    /**
     * @When /^a product is associated with invalid quantified link type$/
     */
    public function aProductWithInvalidQuantifiedLinkType()
    {
        $this->createAndPersistQuantifiedAssociationType('PACK');

        $this->product = $this->createProductFromStandardFormat('yellow_chair', [
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
        Assert::null($this->exception);
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

    private function assertAnInvalidPropertyTypeIsThrown(int $code, string $path = '') {
        Assert::notNull($this->exception);
        Assert::isInstanceOf($this->exception, InvalidPropertyTypeException::class);
        Assert::eq($this->exception->getPropertyName(), $path);
        Assert::eq($this->exception->getCode(), $code);
    }

    private function assertAnInvalidPropertyIsThrown(int $code, string $path = '') {
        Assert::notNull($this->exception);
        Assert::isInstanceOf($this->exception, InvalidPropertyException::class);
        Assert::eq($this->exception->getPropertyName(), $path);
        Assert::eq($this->exception->getCode(), $code);
    }

    private function assertAViolationIsThrown(string $messageTemplate, string $propertyPath) {
        Assert::notNull($this->exception);
        Assert::isInstanceOf($this->exception, ViolationsException::class);
        /** @var $violations ConstraintViolationListInterface */
        $violations = $this->exception->violations();
        Assert::notEmpty($violations);
        foreach ($violations as $violation) {
            Assert::eq($violation->getMessageTemplate(), $messageTemplate);
            Assert::eq($violation->getPropertyPath(), $propertyPath);
        }
    }

    private function assertALegacyViolationIsThrown(string $messageTemplate, string $propertyPath)
    {
        /**
         * @TODO If a LegacyViolationException is thrown, it means that the command was validated.
         * If the command was validated, the productUpdater is called and the resulting product is not valid.
         * It means it miss a validation on the command.
         * This method should never be called.
         */
        Assert::notNull($this->exception);
        Assert::isInstanceOf($this->exception, LegacyViolationsException::class);
        /** @var $violations ConstraintViolationListInterface */
        $violations = $this->exception->violations();
        Assert::notEmpty($violations);
        foreach ($violations as $violation) {
            Assert::eq($violation->getMessageTemplate(), $messageTemplate);
            Assert::eq($violation->getPropertyPath(), $propertyPath);
        }
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
        $this->assertALegacyViolationIsThrown(
            'pim_catalog.constraint.quantified_associations.association_type_does_not_exist',
            'quantifiedAssociations.INVALID_ASSOCIATION_TYPE'
        );
    }

    /**
     * @Then /^this product has a validation error about association type is not quantified$/
     */
    public function thisProductHasAValidationErrorAboutAssociationTypeIsNotQuantified()
    {
        $this->assertALegacyViolationIsThrown(
            'pim_catalog.constraint.quantified_associations.association_type_is_not_quantified',
            'quantifiedAssociations.XSELL'
        );
    }

    /**
     * @Then /^this product has a validation error about invalid quantity$/
     */
    public function thisProductHasAValidationErrorAboutInvalidQuantity()
    {
        $this->assertALegacyViolationIsThrown(
            'pim_catalog.constraint.quantified_associations.invalid_quantity',
            'quantifiedAssociations.PACK.products[0].quantity'
        );
    }

    /**
     * @Then /^this product has a validation error about product do not exist$/
     */
    public function thisProductHasAValidationErrorAboutProductDoNotExist()
    {
        $this->assertALegacyViolationIsThrown(
            'pim_catalog.constraint.quantified_associations.products_do_not_exist',
            'quantifiedAssociations.PACK.products'
        );
    }

    /**
     * @Then /^this product has a validation about product models do not exist$/
     */
    public function thisProductHasAValidationAboutProductModelsDoNotExist()
    {
        $this->assertALegacyViolationIsThrown(
            'pim_catalog.constraint.quantified_associations.product_models_do_not_exist',
            'quantifiedAssociations.PACK.product_models'
        );
    }

    /**
     * @Then /^this product has a validation about maximum number of associations$/
     */
    public function thisProductHasAValidationAboutMaximumNumberOfAssociations()
    {
        $this->assertALegacyViolationIsThrown(
            'pim_catalog.constraint.quantified_associations.max_associations',
            'quantifiedAssociations.PACK'
        );
    }

    /**
     * @Then /^this product has a validation error about unexpected link type$/
     */
    public function thisProductHasAValidationErrorAboutUnexpectedLinkType()
    {
        $this->assertAnInvalidPropertyTypeIsThrown(
            InvalidPropertyTypeException::VALID_ARRAY_STRUCTURE_EXPECTED_CODE,
            /**
             * @TODO The previous path was 'quantifiedAssociations.PACK'. I had to change it to this new value,
             * and I don't know the cause and if it's an issue.
             */
            'quantified_associations'
        );
    }

    /**
     * @When /^I add an association without quantity to this product using a quantified association type$/
     */
    public function iAddAnAssociationWithoutQuantityToThisProductUsingAQuantifiedAssociationType()
    {
        $this->createAndPersistQuantifiedAssociationType('PACK');
        $this->createProduct('accessory');

        $fields = [
            'associations' => [
                'PACK' => [
                    'products' => ['accessory'],
                ],
            ],
        ];

        try {
            $this->updateProductFromStandardFormat($this->product, $fields);
        } catch (Exception $exception) {
            $this->exception = $exception;
        }
    }

    /**
     * @Then /^this product has a validation error about association type should not be quantified$/
     */
    public function thisProductHasAValidationErrorAboutAssociationTypeShouldNotBeQuantified()
    {
        $this->assertAViolationIsThrown(
            'Property "associations" expects a valid association type code. The association type does not exist or is quantified, "PACK" given.',
            'associationUserIntents'
        );
    }

    /**
     * @When /^I add an association without quantity to this product model using a quantified association type$/
     */
    public function iAddAnAssociationWithoutQuantityToThisProductModelUsingAQuantifiedAssociationType()
    {
        $this->createAndPersistQuantifiedAssociationType('PACK');
        $this->createProduct('accessory');

        $fields = [
            'associations' => [
                'PACK' => [
                    'products' => ['accessory'],
                ],
            ],
        ];

        try {
            $this->updateProductModel($this->productModel, $fields);
        } catch (Exception $exception) {
            $this->exception = $exception;
        }
    }

    /**
     * @Then /^this product model has a validation error about association type should not be quantified$/
     */
    public function thisProductModelHasAValidationErrorAboutAssociationTypeShouldNotBeQuantified()
    {
        $this->assertAnInvalidPropertyIsThrown(
            InvalidPropertyException::VALID_ENTITY_CODE_EXPECTED_CODE,
            'associations'
        );
    }

    private function createProduct(string $identifier, array $userIntents = []): ?ProductInterface
    {
        $command = UpsertProductCommand::createWithIdentifier(
            $this->getUserId('admin'),
            ProductIdentifier::fromIdentifier($identifier),
            $userIntents
        );

        try {
            $this->messageBus->dispatch($command);

            return $this->productRepository->findOneByIdentifier($identifier);
        } catch (Exception $e) {
            $this->exception = $e;
        }

        return null;
    }

    private function createProductFromStandardFormat(string $identifier, array $fields): ?ProductInterface
    {
        try {
            $envelope = $this->queryMessageBus->dispatch(new GetUserIntentsFromStandardFormat($fields));
            $handledStamp = $envelope->last(HandledStamp::class);
            $userIntents = $handledStamp->getResult();

            return $this->createProduct($identifier, $userIntents);
        } catch (\Exception $e) {
            $this->exception = $e;
        }

        return null;
    }

    private function updateProductFromStandardFormat(Product $product, array $fields): void
    {
        try {
            $envelope = $this->queryMessageBus->dispatch(new GetUserIntentsFromStandardFormat($fields));
            $handledStamp = $envelope->last(HandledStamp::class);
            $userIntents = $handledStamp->getResult();

            $this->updateProduct($product, $userIntents);
        } catch (\Exception $e) {
            $this->exception = $e;
        }
    }

    private function getUserId(string $username): int
    {
        $user = $this->userRepository->findOneByIdentifier($username);
        Assert::notNull($user);

        return $user->getId();
    }

    private function getIdentifierAttribute(): AttributeInterface
    {
        return $this->attributeRepository->getIdentifier();
    }
}
