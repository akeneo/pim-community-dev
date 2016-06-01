<?php

namespace Pim\Component\Connector\Processor\Denormalization;

use Akeneo\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Akeneo\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Pim\Component\Connector\ArrayConverter\ArrayConverterInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Simple import processor
 *
 * @author    Julien Sanchez <nicolas@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SimpleProcessor extends AbstractProcessor
{
    /** @var ArrayConverterInterface */
    protected $converter;

    /** @var SimpleFactoryInterface */
    protected $factory;

    /** @var ObjectUpdaterInterface */
    protected $updater;

    /** @var ValidatorInterface */
    protected $validator;

    /** @var ObjectDetacherInterface */
    protected $objectDetacher;

    /**
     * @param IdentifiableObjectRepositoryInterface $repository
     * @param ArrayConverterInterface               $converter
     * @param SimpleFactoryInterface                $factory
     * @param ObjectUpdaterInterface                $updater
     * @param ValidatorInterface                    $validator
     * @param ObjectDetacherInterface               $objectDetacher
     */
    public function __construct(
        IdentifiableObjectRepositoryInterface $repository,
        ArrayConverterInterface $converter,
        SimpleFactoryInterface $factory,
        ObjectUpdaterInterface $updater,
        ValidatorInterface $validator,
        ObjectDetacherInterface $objectDetacher
    ) {
        parent::__construct($repository);

        $this->converter      = $converter;
        $this->factory        = $factory;
        $this->updater        = $updater;
        $this->validator      = $validator;
        $this->objectDetacher = $objectDetacher;
    }

    /**
     * {@inheritdoc}
     */
    public function process($item)
    {
        $convertedItem = $this->converter->convert($item);
        $entity        = $this->findOrCreateObject($convertedItem);

        try {
            $this->updater->update($entity, $convertedItem);
        } catch (\InvalidArgumentException $exception) {
            $this->skipItemWithMessage($item, $exception->getMessage(), $exception);
        }

        $violations = $this->validator->validate($entity);
        if ($violations->count() > 0) {
            $this->objectDetacher->detach($entity);
            $this->skipItemWithConstraintViolations($item, $violations);
        }

        return $entity;
    }

    /**
     * @param array $convertedItem
     *
     * @return mixed
     */
    protected function findOrCreateObject(array $convertedItem)
    {
        $entity = $this->findObject($this->repository, $convertedItem);
        if (null === $entity) {
            return $this->factory->create();
        }

        return $entity;
    }
}
