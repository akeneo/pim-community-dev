<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Application;

use Akeneo\Pim\Enrichment\Component\Product\Builder\ProductBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Pim\Enrichment\Product\Api\Command\Exception\LegacyViolationsException;
use Akeneo\Pim\Enrichment\Product\Api\Command\Exception\ViolationsException;
use Akeneo\Pim\Enrichment\Product\Api\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\Api\Command\UserIntent\SetTextValue;
use Akeneo\Tool\Component\StorageUtils\Exception\PropertyException;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @experimental
 *
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
        private ValidatorInterface $productValidator
    ) {
    }

    public function __invoke(UpsertProductCommand $command): void
    {
        /**
         * TODO CPM-492: validate permissions is required here.
         * If we can do that, then the permission are ok for the rest of the code (= use "without permissions" services)
         */
        $violations = $this->validator->validate($command);
        if (0 < $violations->count()) {
            throw new ViolationsException($violations);
        }

        $product = $this->productRepository->findOneByIdentifier($command->productIdentifier());
        if (null === $product) {
            $product = $this->productBuilder->createProduct($command->productIdentifier());
        }

        $this->updateProduct($product, $command);

        // This product validation is legacy. We should remove it as soon all validations will
        // be migrated on the command validation.
        $violations = $this->productValidator->validate($product);
        if (0 < $violations->count()) {
            throw new LegacyViolationsException($violations);
        }

        $this->productSaver->save($product);
    }

    private function updateProduct(ProductInterface $product, UpsertProductCommand $command)
    {
        foreach ($command->valuesUserIntent() as $index => $valueUserIntent) {
            $found = false;
            try {
                if ($valueUserIntent instanceof SetTextValue) {
                    $found = true;
                    $this->productUpdater->update($product, [
                        'values' => [
                            $valueUserIntent->attributeCode() => [
                                [
                                    'locale' => $valueUserIntent->localeCode(),
                                    'scope' => $valueUserIntent->channelCode(),
                                    'data' => $valueUserIntent->value(),
                                ],
                            ],
                        ],
                    ]);
                }
            } catch (PropertyException $e) {
                $violations = new ConstraintViolationList([
                    new ConstraintViolation(
                        $e->getMessage(),
                        $e->getMessage(),
                        [],
                        $command,
                        "valueUserIntent[$index]",
                        $valueUserIntent
                    ),
                ]);

                throw new LegacyViolationsException($violations);
            }

            if (!$found) {
                throw new \InvalidArgumentException(\sprintf('The "%s" intent cannot be handled.', get_class($valueUserIntent)));
            }
        }
    }
}
