<?php

namespace Pim\Component\Connector\Processor\Denormalization;

use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Pim\Component\Catalog\Factory\AttributeGroupFactory;
use Pim\Component\Catalog\Model\AttributeGroupInterface;
use Pim\Component\Connector\ArrayConverter\StandardArrayConverterInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Attribute Group import processor
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeGroupProcessor extends AbstractProcessor
{
    /** @var StandardArrayConverterInterface */
    protected $groupConverter;

    /** @var AttributeGroupFactory */
    protected $groupFactory;

    /** @var ObjectUpdaterInterface */
    protected $updater;

    /** @var ValidatorInterface */
    protected $validator;

    /**
     * @param IdentifiableObjectRepositoryInterface $repository
     * @param StandardArrayConverterInterface       $groupConverter
     * @param AttributeGroupFactory                 $groupFactory
     * @param ObjectUpdaterInterface                $updater
     * @param ValidatorInterface                    $validator
     */
    public function __construct(
        IdentifiableObjectRepositoryInterface $repository,
        StandardArrayConverterInterface $groupConverter,
        AttributeGroupFactory $groupFactory,
        ObjectUpdaterInterface $updater,
        ValidatorInterface $validator
    ) {
        parent::__construct($repository);

        $this->groupConverter = $groupConverter;
        $this->groupFactory   = $groupFactory;
        $this->updater        = $updater;
        $this->validator      = $validator;
    }

    /**
     * {@inheritdoc}
     */
    public function process($item)
    {
        $convertedItem = $this->groupConverter->convert($item);
        $attributeGroup = $this->findOrCreateAttributeGroup($convertedItem);

        try {
            $this->updater->update($attributeGroup, $convertedItem);
        } catch (\InvalidArgumentException $exception) {
            $this->skipItemWithMessage($item, $exception->getMessage(), $exception);
        }

        $violations = $this->validator->validate($attributeGroup);
        if ($violations->count() > 0) {
            $this->skipItemWithConstraintViolations($item, $violations);
        }

        return $attributeGroup;
    }

    /**
     * @param array $convertedItem
     *
     * @return AttributeGroupInterface
     */
    protected function findOrCreateAttributeGroup(array $convertedItem)
    {
        $attributeGroup = $this->findObject($this->repository, $convertedItem);
        if (null === $attributeGroup) {
            return $this->groupFactory->create();
        }

        return $attributeGroup;
    }
}
