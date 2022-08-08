<?php

declare(strict_types=1);

namespace Akeneo\Category\Application;

use Akeneo\Category\Api\Command\Event\CategoryCreatedEvent;
use Akeneo\Category\Api\Command\Event\CategoryUpdatedEvent;
use Akeneo\Category\Api\Command\Exceptions\ViolationsException;
use Akeneo\Category\Api\Command\UpsertCategoryCommand;
use Akeneo\Category\Application\Applier\UserIntentApplierRegistry;
use Akeneo\Category\Application\Query\FindCategoryByCode;
use Akeneo\Category\Domain\Model\Category;
use Exception;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UpsertCategoryCommandHandler
{
    public function __construct(
        private FindCategoryByCode $findCategoryByCode,
        private UserIntentApplierRegistry $applierRegistry,
        private EventDispatcherInterface $eventDispatcher
    ) {
    }

    /**
     * @throws Exception
     */
    public function __invoke(UpsertCategoryCommand $command): void
    {
        //  TODO : validate command (symfony validation)

        //  Get the category
        $category = $this->findCategoryByCode->__invoke($command->categoryCode());

        $isCreation = null === $category;
        // TODO : Manage to create category
        if ($isCreation) {
            throw new Exception("Command to create a category is in progress.");
        }

        $this->updateCategory($category, $command);

        // TODO : Save Model in db

        // TODO : Ensure use event correctly
        if ($isCreation) {
            $this->eventDispatcher->dispatch(new CategoryCreatedEvent((string)$category->getCode()));
        } else {
            $this->eventDispatcher->dispatch(new CategoryUpdatedEvent((string)$category->getCode()));
        }
    }

    private function updateCategory(Category $category, UpsertCategoryCommand $command): void
    {
        foreach ($command->userIntents() as $userIntent) {
            $applier = $this->applierRegistry->getApplier($userIntent);
            if (null === $applier) {
                throw new \InvalidArgumentException(
                    \sprintf('The "%s" intent cannot be handled.', get_class($userIntent))
                );
            }

            try {
                $applier->apply($userIntent, $category);
            } catch (\LogicException $exception) {
                $violations = new ConstraintViolationList([
                    new ConstraintViolation(
                        $exception->getMessage(),
                        $exception->getMessage(),
                        [],
                        $command,
                        $applier::class,
                        $userIntent
                    ),
                ]);

                throw new ViolationsException($violations);
            }
        }
    }
}
