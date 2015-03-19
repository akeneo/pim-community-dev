<?php

namespace Pim\Bundle\BaseConnectorBundle\Processor\Denormalization;

use Akeneo\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Akeneo\Bundle\StorageUtilsBundle\Repository\IdentifiableObjectRepositoryInterface;
use Pim\Bundle\BaseConnectorBundle\Processor\Denormalization\Converter\StandardArrayConverterInterface;
use Pim\Bundle\CatalogBundle\Factory\AttributeOptionFactory;
use Pim\Bundle\CatalogBundle\Model\AttributeOptionInterface;
use Pim\Bundle\CatalogBundle\Updater\AttributeOptionUpdaterInterface;
use Symfony\Component\Validator\ValidatorInterface;

/**
 * AttributeOption option import processor, allows to,
 *  - create / update attributeOption options
 *  - return the valid attributeOption options, throw exceptions to skip invalid ones
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeOptionProcessor extends AbstractReworkedProcessor
{
    /** @staticvar string */
    const OPTION_CODE_FIELD = 'code';

    /** @staticvar string */
    const ATTRIBUTE_CODE_FIELD = 'attribute';

    /** @var StandardArrayConverterInterface */
    protected $arrayConverter;

    /** @var AttributeOptionFactory */
    protected $optionFactory;

    /** @var AttributeOptionUpdaterInterface */
    protected $optionUpdater;

    /**
     * @param StandardArrayConverterInterface       $arrayConverter   format converter
     * @param IdentifiableObjectRepositoryInterface $optionRepository option repository
     * @param AttributeOptionFactory                $optionFactory    option factory
     * @param AttributeOptionUpdaterInterface       $optionUpdater    option updater
     * @param ValidatorInterface                    $validator        validator of the object
     * @param ObjectDetacherInterface               $detacher         detacher to remove it from UOW when skip
     */
    public function __construct(
        StandardArrayConverterInterface $arrayConverter,
        IdentifiableObjectRepositoryInterface $optionRepository,
        AttributeOptionFactory $optionFactory,
        AttributeOptionUpdaterInterface $optionUpdater,
        ValidatorInterface $validator,
        ObjectDetacherInterface $detacher
    ) {
        parent::__construct($optionRepository, $validator, $detacher);
        $this->arrayConverter = $arrayConverter;
        $this->optionFactory = $optionFactory;
        $this->optionUpdater = $optionUpdater;
    }

    /**
     * {@inheritdoc}
     */
    public function process($item)
    {
        $convertedItem = $this->convertItemData($item);
        /** @var AttributeOptionInterface $attributeOption */
        $attributeOption = $this->findOrCreateAttributeOption($convertedItem);
        $this->updateAttributeOption($attributeOption, $convertedItem);
        $this->validateAttributeOption($attributeOption, $convertedItem);

        return $attributeOption;
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
     * @return AttributeOptionInterface
     */
    protected function findOrCreateAttributeOption(array $convertedItem)
    {
        /** @var AttributeOptionInterface $attributeOption */
        $attributeOption = $this->findObject($this->repository, $convertedItem);
        if ($attributeOption === null) {
            return $this->optionFactory->createAttributeOption();
        }

        return $attributeOption;
    }

    /**
     * @param AttributeOptionInterface $attributeOption
     * @param array                    $convertedItem
     *
     * @return AttributeOptionInterface
     */
    protected function updateAttributeOption(AttributeOptionInterface $attributeOption, array $convertedItem)
    {
        $isNew = $attributeOption->getId() === null;
        $readOnlyFields = [self::ATTRIBUTE_CODE_FIELD, self::OPTION_CODE_FIELD];
        foreach ($convertedItem as $field => $data) {
            $isReadOnlyField = in_array($field, $readOnlyFields);
            if ($isNew) {
                $this->optionUpdater->setData($attributeOption, $field, $data);
            } elseif (false === $isReadOnlyField) {
                $this->optionUpdater->setData($attributeOption, $field, $data);
            }
        }

        return $attributeOption;
    }

    /**
     * @param AttributeOptionInterface $attributeOption
     * @param array                    $item
     */
    protected function validateAttributeOption(AttributeOptionInterface $attributeOption, array $item)
    {
        // TODO: ugly fix to workaround issue with "attribute.group.code: This value should not be blank."
        $attributeOption->getAttribute()->getGroup()->getCode();

        $violations = $this->validator->validate($attributeOption);
        if ($violations->count() !== 0) {
            $this->detachObject($attributeOption);
            $this->skipItemWithConstraintViolations($item, $violations);
        }
    }
}
