<?php

namespace Pim\Bundle\BaseConnectorBundle\Processor\Denormalization;

use Akeneo\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Akeneo\Bundle\StorageUtilsBundle\Repository\IdentifiableObjectRepositoryInterface;
use Pim\Bundle\BaseConnectorBundle\Processor\Denormalization\Converter\StandardArrayConverterInterface;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\AttributeOptionInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
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
class AttributeOptionProcessor extends AbstractProcessor
{
    /** @staticvar string */
    const ATTRIBUTE_CODE_FIELD = 'attribute';

    /** @staticvar string */
    const CODE_FIELD = 'code';

    /** @var IdentifiableObjectRepositoryInterface */
    protected $attributeRepository;

    /** @var StandardArrayConverterInterface */
    protected $formatConverter;

    /**
     * @param StandardArrayConverterInterface       $formatConverter     format converter
     * @param IdentifiableObjectRepositoryInterface $optionRepository    option repository to search the object in
     * @param IdentifiableObjectRepositoryInterface $attributeRepository attribute repository to search the object in
     * @param DenormalizerInterface                 $denormalizer        denormalizer used to transform array to object
     * @param ValidatorInterface                    $validator           validator of the object
     * @param ObjectDetacherInterface               $detacher            detacher to remove it from UOW when skip
     * @param string                                $class               class of the object to instanciate in case if need
     */
    public function __construct(
        StandardArrayConverterInterface $formatConverter,
        IdentifiableObjectRepositoryInterface $optionRepository,
        IdentifiableObjectRepositoryInterface $attributeRepository,
        DenormalizerInterface $denormalizer,
        ValidatorInterface $validator,
        ObjectDetacherInterface $detacher,
        $class
    ) {
        parent::__construct($optionRepository, $denormalizer, $validator, $detacher, $class);
        $this->attributeRepository = $attributeRepository;
        $this->formatConverter = $formatConverter;
    }

    /**
     * {@inheritdoc}
     */
    public function process($item)
    {
        $convertedItem = $this->convertItemData($item);
        $this->checkItemData($convertedItem, $item);
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
        return $this->formatConverter->convert($item);
    }

    /**
     * @param array $convertedItem
     */
    protected function checkItemData(array $convertedItem, array $item)
    {
        if (!isset($convertedItem[self::CODE_FIELD]) || empty($convertedItem[self::CODE_FIELD])) {
            $this->skipItemWithMessage($item, 'Option code must be provided');
        }
        if (!isset($convertedItem[self::ATTRIBUTE_CODE_FIELD]) || empty($convertedItem[self::ATTRIBUTE_CODE_FIELD])) {
            $this->skipItemWithMessage($item, 'Attribute code must be provided');
        }
    }

    /**
     * Find or create the group
     *
     * @param array $convertedItem
     *
     * @return AttributeOptionInterface
     */
    protected function findOrCreateAttributeOption(array $convertedItem)
    {
        /** @var AttributeInterface $attribute */
        $attribute = $this->attributeRepository->findOneByIdentifier($convertedItem[self::ATTRIBUTE_CODE_FIELD]);
        if (null === $attribute) {
            throw new \InvalidArgumentException(
                sprintf('Argument with code "%s" does not exists', $convertedItem[self::ATTRIBUTE_CODE_FIELD])
            );
        }

        /** @var AttributeOptionInterface $attributeOption */
        $attributeOption = $this->findOrCreateObject($this->repository, $convertedItem, $this->class);
        $attributeOption->setCode($convertedItem[self::CODE_FIELD]);
        $attributeOption->setAttribute($attribute);

        return $attributeOption;
    }

    /**
     * Update the variant group fields
     *
     * @param AttributeOptionInterface $attributeOption
     * @param array                    $attributeOptionData
     *
     * @return AttributeOptionInterface
     */
    protected function updateAttributeOption(AttributeOptionInterface $attributeOption, array $attributeOptionData)
    {
        $attributeOption = $this->denormalizer->denormalize(
            $attributeOptionData,
            $this->class,
            null, // TODO useless here, could pass internal standard format!
            ['object' => $attributeOption]
        );

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
