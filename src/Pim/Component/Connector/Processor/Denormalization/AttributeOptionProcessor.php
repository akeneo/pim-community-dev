<?php

namespace Pim\Component\Connector\Processor\Denormalization;

use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Pim\Component\Catalog\Model\AttributeOptionInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Attribute option import processor, allows to,
 *  - create / update
 *  - validate
 *  - skip invalid ones
 *  - return the valid ones
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeOptionProcessor extends AbstractProcessor
{
    /** @var ObjectUpdaterInterface */
    protected $updater;

    /** @var ValidatorInterface */
    protected $validator;

    /** @var string */
    protected $class;

    /**
     * @param IdentifiableObjectRepositoryInterface $repository     attribute option repository
     * @param ObjectUpdaterInterface                $updater        attribute option updater
     * @param ValidatorInterface                    $validator      attribute option validator
     * @param string                                $class          attribute option class
     */
    public function __construct(
        IdentifiableObjectRepositoryInterface $repository,
        ObjectUpdaterInterface $updater,
        ValidatorInterface $validator,
        $class
    ) {
        parent::__construct($repository);

        $this->updater = $updater;
        $this->validator = $validator;
        $this->class = $class;
    }

    /**
     * {@inheritdoc}
     */
    public function process($item)
    {
        $attributeOption = $this->findOrCreateAttributeOption($item);
        try {
            $this->updateAttributeOption($attributeOption, $item);
        } catch (\InvalidArgumentException $exception) {
            $this->skipItemWithMessage($item, $exception->getMessage(), $exception);
        }

        $violations = $this->validateAttributeOption($attributeOption);
        if ($violations->count() > 0) {
            $this->skipItemWithConstraintViolations($item, $violations);
        }

        return $attributeOption;
    }

    /**
     * @param array $convertedItem
     *
     * @return AttributeOptionInterface
     */
    protected function findOrCreateAttributeOption(array $convertedItem)
    {
        $attributeOption = $this->findObject($this->repository, $convertedItem);
        if ($attributeOption === null) {
            return new $this->class();
        }

        return $attributeOption;
    }

    /**
     * @param AttributeOptionInterface $attributeOption
     * @param array                    $convertedItem
     *
     * @throws \InvalidArgumentException
     */
    protected function updateAttributeOption(AttributeOptionInterface $attributeOption, array $convertedItem)
    {
        $this->updater->update($attributeOption, $convertedItem);
    }

    /**
     * @param AttributeOptionInterface $attributeOption
     *
     * @throws \InvalidArgumentException
     *
     * @return ConstraintViolationListInterface
     */
    protected function validateAttributeOption(AttributeOptionInterface $attributeOption)
    {
        // TODO: ugly fix to workaround issue with "attribute.group.code: This value should not be blank."
        // in case of existing option, attribute is a proxy, attribute group too, the validated group code is null
        (null !== $attributeOption->getAttribute()) ? $attributeOption->getAttribute()->getGroup()->getCode() : null;

        return $this->validator->validate($attributeOption);
    }
}
