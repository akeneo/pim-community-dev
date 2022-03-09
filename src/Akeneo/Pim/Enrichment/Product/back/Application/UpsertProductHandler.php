<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Application;

use Akeneo\Pim\Enrichment\Component\Product\Builder\ProductBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Pim\Enrichment\Product\API\Command\Exception\LegacyViolationsException;
use Akeneo\Pim\Enrichment\Product\API\Command\Exception\ViolationsException;
use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\AddMultiSelectValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ClearValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\RemoveFamily;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetBooleanValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetCategories;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetDateValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetEnabled;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetFamily;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetMetricValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetMultiSelectValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetNumberValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetSimpleSelectValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetTextareaValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetTextValue;
use Akeneo\Pim\Enrichment\Product\API\Event\ProductWasCreated;
use Akeneo\Pim\Enrichment\Product\API\Event\ProductWasUpdated;
use Akeneo\Pim\Enrichment\Product\Application\Applier\UserIntentApplierRegistry;
use Akeneo\Tool\Component\StorageUtils\Exception\PropertyException;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class UpsertProductHandler
{
    public function __construct(
        private ValidatorInterface $validator,
        private ProductRepositoryInterface $productRepository,
        private ProductBuilderInterface $productBuilder,
        private SaverInterface $productSaver,
        private ObjectUpdaterInterface $productUpdater,
        private ValidatorInterface $productValidator,
        private EventDispatcherInterface $eventDispatcher,
        private UserIntentApplierRegistry $applierRegistry
    ) {
    }

    public function __invoke(UpsertProductCommand $command): void
    {
        $violations = $this->validator->validate($command);
        if (0 < $violations->count()) {
            throw new ViolationsException($violations);
        }

        $product = $this->productRepository->findOneByIdentifier($command->productIdentifier());
        $isCreation = false;
        if (null === $product) {
            $isCreation = true;
            $product = $this->productBuilder->createProduct($command->productIdentifier());
        }

        $this->updateProduct($product, $command);

        // This product validation is legacy. We should remove it as soon all validations will
        // be migrated on the command validation.
        $violations = $this->productValidator->validate($product);
        if (0 < $violations->count()) {
            throw new LegacyViolationsException($violations);
        }

        $isUpdate = $product->isDirty();
        $this->productSaver->save($product);

        if ($isCreation) {
            $this->eventDispatcher->dispatch(new ProductWasCreated($product->getIdentifier()));
        } elseif ($isUpdate) {
            $this->eventDispatcher->dispatch(new ProductWasUpdated($product->getIdentifier()));
        }
    }

    private function updateProduct(ProductInterface $product, UpsertProductCommand $command): void
    {
        $userIntents = array_filter(
            [
                ...$command->valueUserIntents(),
                $command->enabledUserIntent(),
                $command->familyUserIntent(),
            ],
            fn ($userIntent): bool => null !== $userIntent
        );

        foreach ($userIntents as $index => $userIntent) {
            $applier = $this->applierRegistry->getApplier($userIntent);
            if (null !== $applier) {
                $applier->apply($userIntent, $product, $command->userId());
            } else {
                if (
                    $userIntent instanceof SetTextValue
                    || $userIntent instanceof SetNumberValue
                    || $userIntent instanceof SetTextareaValue
                    || $userIntent instanceof SetBooleanValue
                    || $userIntent instanceof SetSimpleSelectValue
                ) {
                    $data = [
                        'values' => [
                            $userIntent->attributeCode() => [
                                [
                                    'locale' => $userIntent->localeCode(),
                                    'scope' => $userIntent->channelCode(),
                                    'data' => $userIntent->value(),
                                ],
                            ],
                        ],
                    ];
                    $propertyPath = sprintf('valueUserIntents[%d]', $index);
                } elseif ($userIntent instanceof SetMetricValue) {
                    $data = [
                        'values' => [
                            $userIntent->attributeCode() => [
                                [
                                    'locale' => $userIntent->localeCode(),
                                    'scope' => $userIntent->channelCode(),
                                    'data' => [
                                        'amount' => $userIntent->amount(),
                                        'unit' => $userIntent->unit(),
                                    ],
                                ],
                            ],
                        ],
                    ];
                    $propertyPath = sprintf('valueUserIntents[%d]', $index);
                } elseif ($userIntent instanceof SetMultiSelectValue) {
                    $data = [
                        'values' => [
                            $userIntent->attributeCode() => [
                                [
                                    'locale' => $userIntent->localeCode(),
                                    'scope' => $userIntent->channelCode(),
                                    'data' => $userIntent->values(),
                                ],
                            ],
                        ],
                    ];
                    $propertyPath = sprintf('valueUserIntents[%d]', $index);
                } elseif ($userIntent instanceof ClearValue) {
                    $data = [
                        'values' => [
                            $userIntent->attributeCode() => [
                                [
                                    'locale' => $userIntent->localeCode(),
                                    'scope' => $userIntent->channelCode(),
                                    'data' => null,
                                ],
                            ],
                        ],
                    ];
                    $propertyPath = sprintf('valueUserIntents[%d]', $index);
                } elseif ($userIntent instanceof SetDateValue) {
                    $data = [
                        'values' => [
                            $userIntent->attributeCode() => [
                                [
                                    'locale' => $userIntent->localeCode(),
                                    'scope' => $userIntent->channelCode(),
                                    'data' => $userIntent->value()->format('Y-m-d'),
                                ],
                            ],
                        ],
                    ];
                    $propertyPath = sprintf('valueUserIntents[%d]', $index);
                } elseif ($userIntent instanceof AddMultiSelectValue) {
                    $formerValue = $product->getValue(
                        $userIntent->attributeCode(),
                        $userIntent->localeCode(),
                        $userIntent->channelCode()
                    );

                    $values = null !== $formerValue ?
                        \array_unique(\array_merge($formerValue->getData(), $userIntent->optionCodes())) :
                        $userIntent->optionCodes();

                    $data = [
                        'values' => [
                            $userIntent->attributeCode() => [
                                [
                                    'locale' => $userIntent->localeCode(),
                                    'scope' => $userIntent->channelCode(),
                                    'data' => $values,
                                ],
                            ],
                        ],
                    ];
                    $propertyPath = sprintf('valueUserIntents[%d]', $index);
                } elseif ($userIntent instanceof SetFamily) {
                    $data = ['family' => $userIntent->familyCode()];
                    $propertyPath = 'familyUserIntent';
                } elseif ($userIntent instanceof RemoveFamily) {
                    $data = ['family' => null];
                    $propertyPath = 'familyUserIntent';
                } else {
                    throw new \InvalidArgumentException(\sprintf('The "%s" intent cannot be handled.', get_class($userIntent)));
                }

                try {
                    $this->productUpdater->update($product, $data);
                } catch (PropertyException $e) {
                    $violations = new ConstraintViolationList([
                        new ConstraintViolation(
                            $e->getMessage(),
                            $e->getMessage(),
                            [],
                            $command,
                            $propertyPath,
                            $userIntent
                        ),
                    ]);

                    throw new ViolationsException($violations);
                }
            }
        }

        if ($command->categoryUserIntent() instanceof SetCategories) {
            $this->productUpdater->update($product, [
                'categories' => $command->categoryUserIntent()->categoriesCodes(),
            ]);
        }
    }
}
