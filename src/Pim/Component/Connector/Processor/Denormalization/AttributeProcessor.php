<?php

namespace Pim\Component\Connector\Processor\Denormalization;

use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Pim\Bundle\CatalogBundle\Factory\AttributeFactory;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Component\Connector\ArrayConverter\StandardArrayConverterInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
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
    /** @var StandardArrayConverterInterface */
    protected $arrayConverter;

    /** @var ObjectUpdaterInterface */
    protected $updater;

    /** @var ValidatorInterface */
    protected $validator;

    /** @var AttributeFactory */
    protected $attributeFactory;

    /**
     * @param StandardArrayConverterInterface       $arrayConverter
     * @param IdentifiableObjectRepositoryInterface $repository
     * @param AttributeFactory                      $attributeFactory
     * @param ObjectUpdaterInterface                $updater
     * @param ValidatorInterface                    $validator
     */
    public function __construct(
        StandardArrayConverterInterface $arrayConverter,
        IdentifiableObjectRepositoryInterface $repository,
        AttributeFactory $attributeFactory,
        ObjectUpdaterInterface $updater,
        ValidatorInterface $validator
    ) {
        parent::__construct($repository);

        $this->arrayConverter    = $arrayConverter;
        $this->attributeFactory  = $attributeFactory;
        $this->updater           = $updater;
        $this->validator         = $validator;
    }

    /**
     * {@inheritdoc}
     */
    public function process($item)
    {
        $convertedItem = $this->convertItemData($item);
        $attribute = $this->findOrCreateAttribute($convertedItem);

        try {
            $this->updateAttribute($attribute, $convertedItem);
        } catch (\InvalidArgumentException $exception) {
            $this->skipItemWithMessage($item, $exception->getMessage(), $exception);
        }

        $violations = $this->validateAttribute($attribute);
        if ($violations->count() > 0) {
            $this->skipItemWithConstraintViolations($item, $violations);
        }

        return $attribute;
    }

    /**
     * @param array $item
     *
     * @return array
     */
    protected function convertItemData(array $item)
    {
        return $this->arrayConverter->convert($item);
    }

    /**
     * @param array $convertedItem
     *
     * @return AttributeInterface
     */
    protected function findOrCreateAttribute(array $convertedItem)
    {
        $attribute = $this->findObject($this->repository, $convertedItem);
        if (null === $attribute) {
            return $this->attributeFactory->createAttribute($convertedItem['attributeType']);
        }

        return $attribute;
    }

    /**
     * @param AttributeInterface $attribute
     * @param array              $convertedItem
     *
     * @throws \InvalidArgumentException
     */
    protected function updateAttribute(AttributeInterface $attribute, array $convertedItem)
    {
        $this->updater->update($attribute, $convertedItem);
    }

    /**
     * @param AttributeInterface $attribute
     *
     * @throws \InvalidArgumentException
     *
     * @return ConstraintViolationListInterface
     */
    protected function validateAttribute(AttributeInterface $attribute)
    {
        return $this->validator->validate($attribute);
    }
}
