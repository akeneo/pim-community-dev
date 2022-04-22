<?php

namespace Akeneo\Category\Infrastructure\Doctrine;

use Akeneo\Category\API\Command\CreateCategoryCommand;
use Akeneo\Category\Domain\Exception\InvalidPropertyException;
use Akeneo\Category\Domain\Exception\ViolationsException;
use Akeneo\Tool\Component\StorageUtils\Exception\PropertyException;
use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class DoctrineCreateCategoryHandler
{
    public function __construct(
        protected SimpleFactoryInterface $factory,
        protected ObjectUpdaterInterface $updater,
        protected ValidatorInterface $validator,
        protected SaverInterface $saver,
    ) {
    }

    public function __invoke(CreateCategoryCommand $command): void
    {
        $category = $this->factory->create();

        try {
            $this->updater->update($category, [
                'code' => $command->getCode(),
                'labels' => $command->getLabels(),
                'parent' => $command->getParent(),
            ]);
        } catch (PropertyException $exception) {
            throw new InvalidPropertyException($exception->getMessage(), $exception->getCode(), $exception);
        }

        $violations = $this->validator->validate($category);
        if (0 !== $violations->count()) {
            throw new ViolationsException($violations);
        }

        $this->saver->save($category);
    }
}
