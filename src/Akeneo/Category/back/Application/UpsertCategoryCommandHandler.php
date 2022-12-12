<?php

declare(strict_types=1);

namespace Akeneo\Category\Application;

use Akeneo\Category\Api\Command\Exceptions\ViolationsException;
use Akeneo\Category\Api\Command\UpsertCategoryCommand;
use Akeneo\Category\Api\Event\CategoryCreatedEvent;
use Akeneo\Category\Application\Applier\UserIntentApplierRegistry;
use Akeneo\Category\Application\Storage\Save\CategorySaverProcessor;
use Akeneo\Category\Domain\Model\Enrichment\Category;
use Akeneo\Category\Domain\Query\GetCategoryInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UpsertCategoryCommandHandler
{
    public function __construct(
        private ValidatorInterface $validator,
        private GetCategoryInterface $getCategory,
        private UserIntentApplierRegistry $applierRegistry,
        private EventDispatcherInterface $eventDispatcher,
        private CategorySaverProcessor $saver,
    ) {
    }

    /**
     * @throws \Exception
     */
    public function __invoke(UpsertCategoryCommand $command): void
    {
        $this->validateCommand($command);

        $category = $this->getCategory->byCode($command->categoryCode());

        $isCreation = null === $category;
        if ($isCreation) {
            throw new \Exception('Command to create a category is in progress.');
        }

        $this->updateCategory($category, $command);

        $this->saver->save($category, $command->userIntents());

        $this->eventDispatcher->dispatch(new CategoryCreatedEvent((string) $category->getCode()));
    }

    private function updateCategory(Category $category, UpsertCategoryCommand $command): void
    {
        foreach ($command->userIntents() as $userIntent) {
            $applier = $this->applierRegistry->getApplier($userIntent);
            if (null === $applier) {
                throw new \InvalidArgumentException(\sprintf('The "%s" intent cannot be handled.', get_class($userIntent)));
            }

            try {
                $applier->apply($userIntent, $category);
            } catch (\LogicException $exception) {
                $violations = new ConstraintViolationList([
                    new ConstraintViolation(
                        message: $exception->getMessage(),
                        messageTemplate: $exception->getMessage(),
                        parameters: [],
                        root: $command,
                        propertyPath: $applier::class,
                        invalidValue: $userIntent,
                    ),
                ]);

                throw new ViolationsException($violations);
            }
        }
    }

    private function validateCommand(UpsertCategoryCommand $command): void
    {
        $violations = $this->validator->validate($command);
        if (0 < $violations->count()) {
            throw new ViolationsException($violations);
        }
    }
}
