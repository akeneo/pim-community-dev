<?php

namespace Pim\Component\Connector\Processor\Denormalization;

use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Pim\Component\Catalog\Factory\AttributeFactory;
use Pim\Component\Connector\ArrayConverter\ArrayConverterInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Attribute import processor
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeProcessor extends AbstractProcessor
{
    /** @var ArrayConverterInterface */
    protected $converter;

    /** @var ObjectUpdaterInterface */
    protected $updater;

    /** @var ValidatorInterface */
    protected $validator;

    /** @var AttributeFactory */
    protected $factory;

    /**
     * @param IdentifiableObjectRepositoryInterface $repository
     * @param ArrayConverterInterface               $converter
     * @param AttributeFactory                      $factory
     * @param ObjectUpdaterInterface                $updater
     * @param ValidatorInterface                    $validator
     */
    public function __construct(
        IdentifiableObjectRepositoryInterface $repository,
        ArrayConverterInterface $converter,
        AttributeFactory $factory,
        ObjectUpdaterInterface $updater,
        ValidatorInterface $validator
    ) {
        parent::__construct($repository);

        $this->converter = $converter;
        $this->factory   = $factory;
        $this->updater   = $updater;
        $this->validator = $validator;
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
            return $this->factory->createAttribute($convertedItem['attributeType']);
        }

        return $entity;
    }
}
