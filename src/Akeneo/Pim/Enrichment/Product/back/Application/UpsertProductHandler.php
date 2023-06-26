<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Application;

use Akeneo\Pim\Enrichment\Component\Product\Builder\ProductBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Pim\Enrichment\Product\API\Command\Exception\LegacyViolationsException;
use Akeneo\Pim\Enrichment\Product\API\Command\Exception\ViolationsException;
use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\API\Event\ProductWasCreated;
use Akeneo\Pim\Enrichment\Product\API\Event\ProductWasUpdated;
use Akeneo\Pim\Enrichment\Product\API\ValueObject\ProductIdentifier;
use Akeneo\Pim\Enrichment\Product\API\ValueObject\ProductUuid;
use Akeneo\Pim\Enrichment\Product\Application\Applier\UserIntentApplierRegistry;
use Akeneo\Tool\Component\StorageUtils\Exception\PropertyException;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Webmozart\Assert\Assert;

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
        private ValidatorInterface $productValidator,
        private EventDispatcherInterface $eventDispatcher,
        private UserIntentApplierRegistry $applierRegistry,
        private TokenStorageInterface $tokenStorage,
    ) {
    }

    public function __invoke(UpsertProductCommand $command): void
    {
        $violations = $this->validator->validate($command);
        if (0 < $violations->count()) {
            throw new ViolationsException($violations);
        }

        $this->checkConsistencyWithConnectedUser($command->userId());

        if ($command->productIdentifierOrUuid() instanceof ProductIdentifier) {
            $product = $this->productRepository->findOneByIdentifier($command->productIdentifierOrUuid()->identifier())
                ?? $this->productBuilder->createProduct(identifier: $command->productIdentifierOrUuid()->identifier());
        } elseif ($command->productIdentifierOrUuid() instanceof ProductUuid) {
            $product = $this->productRepository->find($command->productIdentifierOrUuid()->uuid())
                ?? $this->productBuilder->createProduct(uuid: $command->productIdentifierOrUuid()->uuid()->toString());
        } else {
            $product = $this->productBuilder->createProduct();
        }
        Assert::isInstanceOf($product, ProductInterface::class);

        $isCreation = (null === $product->getCreated());

        $this->updateProduct($product, $command);

        // This product validation is legacy. We should remove it as soon all validations will
        // be migrated on the command validation.
        $violations = $this->productValidator->validate($product);
        if (0 < $violations->count()) {
            throw new LegacyViolationsException($violations);
        }

        if ($command->dryRun()) {
            return;
        }

        $isUpdate = $product->isDirty();
        $this->productSaver->save($product);

        if ($isCreation) {
            $this->eventDispatcher->dispatch(new ProductWasCreated($product->getUuid()));
        } elseif ($isUpdate) {
            $this->eventDispatcher->dispatch(new ProductWasUpdated(
                $product->getUuid(),
                \DateTimeImmutable::createFromMutable($product->getUpdated())
            ));
        }
    }

    private function updateProduct(ProductInterface $product, UpsertProductCommand $command): void
    {
        $indexedValueUserIntents = \array_combine(
            \array_map(
                fn (mixed $key): string => \sprintf('valueUserIntents[%s]', $key),
                \array_keys($command->valueUserIntents())
            ),
            $command->valueUserIntents()
        );
        $userIntents = \array_filter(
            array_merge(
                [
                    'parentUserIntent' => $command->parentUserIntent(),
                ],
                $indexedValueUserIntents,
                [
                    'enabledUserIntent' => $command->enabledUserIntent(),
                    'familyUserIntent' => $command->familyUserIntent(),
                    'categoryUserIntent' => $command->categoryUserIntent(),
                    'groupUserIntent' => $command->groupUserIntent(),
                    'associationUserIntents' => $command->associationUserIntents(),
                    'quantifiedAssociationUserIntents' => $command->quantifiedAssociationUserIntents(),
                ]
            ),
            fn ($userIntent): bool => null !== $userIntent
        );

        foreach ($userIntents as $propertyPath => $userIntent) {
            $applier = $this->applierRegistry->getApplier($userIntent);
            if (null !== $applier) {
                try {
                    $applier->apply($userIntent, $product, $command->userId());
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
            } else {
                throw new \InvalidArgumentException(\sprintf('The "%s" intent cannot be handled.', get_class($userIntent)));
            }
        }
    }

    /**
     * The new service API is for now a wrapper of the legacy implementation to update a product. The legacy
     * implementation use in some services the user in the token storage and not the user id of the command (mainly to check permissions).
     * That's why, for now, we check that the user in the token storage is consistent with the provided user id.
     *
     * Once we re-implement the legacy implementation to not rely on the token storage, this check and the injection of
     * the token storage should be removed. Indeed, for the token storage make this service API stateful and not agnostic of the user.
     */
    private function checkConsistencyWithConnectedUser(int $userId): void
    {
        $user = $this->tokenStorage->getToken()?->getUser();
        Assert::implementsInterface($user, UserInterface::class);

        if ($userId !== $user->getId()) {
            throw new \LogicException('User id provided to the command is not the same as the connected user');
        }
    }
}
