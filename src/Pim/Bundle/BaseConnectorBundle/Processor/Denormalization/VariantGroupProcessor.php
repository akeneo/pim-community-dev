<?php

namespace Pim\Bundle\BaseConnectorBundle\Processor\Denormalization;

use Akeneo\Bundle\BatchBundle\Item\InvalidItemException;
use Akeneo\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Akeneo\Bundle\StorageUtilsBundle\Repository\IdentifiableObjectRepositoryInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Pim\Bundle\CatalogBundle\Manager\ProductTemplateMediaManager;
use Pim\Bundle\CatalogBundle\Model\GroupInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\ValidatorInterface;

/**
 * Variant group import processor, allows to,
 *  - create / update variant groups
 *  - bind values data into a product template linked to a variant group
 *  - validate values and save values in template (it erases existing values)
 *  - return the valid variant groups, throw exceptions to skip invalid ones
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class VariantGroupProcessor extends AbstractProcessor
{
    /** @staticvar string */
    const CODE_FIELD = 'code';

    /** @staticvar string */
    const TYPE_FIELD = 'type';

    /** @staticvar string */
    const AXIS_FIELD = 'axis';

    /** @staticvar string */
    const LABEL_PATTERN = 'label-';

    /** @var DenormalizerInterface */
    protected $denormalizer;

    /** @var ObjectDetacherInterface */
    protected $detacher;

    /** @var NormalizerInterface */
    protected $normalizer;

    /** @var ProductTemplateMediaManager */
    protected $templateMediaManager;

    /** @var string */
    protected $templateClass;

    /** @var string */
    protected $format;

    /** @var string */
    protected $class;

    /**
     * @param IdentifiableObjectRepositoryInterface $groupRepository
     * @param DenormalizerInterface                 $denormalizer
     * @param ValidatorInterface                    $validator
     * @param ObjectDetacherInterface               $detacher
     * @param NormalizerInterface                   $normalizer
     * @param ProductTemplateMediaManager           $templateMediaManager
     * @param string                                $groupClass
     * @param string                                $templateClass
     * @param string                                $format
     */
    public function __construct(
        IdentifiableObjectRepositoryInterface $groupRepository,
        DenormalizerInterface $denormalizer,
        ValidatorInterface $validator,
        ObjectDetacherInterface $detacher,
        NormalizerInterface $normalizer,
        ProductTemplateMediaManager $templateMediaManager,
        $groupClass,
        $templateClass,
        $format
    ) {
        parent::__construct($groupRepository, $validator);
        $this->denormalizer         = $denormalizer;
        $this->detacher             = $detacher;
        $this->normalizer           = $normalizer;
        $this->templateMediaManager = $templateMediaManager;
        $this->templateClass        = $templateClass;
        $this->format               = $format;
        $this->class                = $groupClass;
    }

    /**
     * {@inheritdoc}
     */
    public function process($item)
    {
        $item[self::TYPE_FIELD] = 'VARIANT';
        $variantGroup = $this->findOrCreateVariantGroup($item);
        $this->updateVariantGroup($variantGroup, $item);
        $this->updateVariantGroupValues($variantGroup, $item);
        $this->validateVariantGroup($variantGroup, $item);

        return $variantGroup;
    }

    /**
     * Find or create the variant group
     *
     * @param array $groupData
     *
     * @return GroupInterface
     */
    protected function findOrCreateVariantGroup(array $groupData)
    {
        if (null === $variantGroup = $this->findObject($this->repository, $groupData)) {
            $variantGroup = new $this->class();
        }

        $isExistingGroup = (null !== $variantGroup->getType() && false === $variantGroup->getType()->isVariant());
        if ($isExistingGroup) {
            $this->skipItemWithMessage(
                $groupData,
                sprintf('Cannot process group "%s", only variant groups are accepted', $groupData[self::CODE_FIELD])
            );
        }

        return $variantGroup;
    }

    /**
     * Update the variant group fields
     *
     * @param GroupInterface $variantGroup
     * @param array          $groupData
     *
     * @return GroupInterface
     */
    protected function updateVariantGroup(GroupInterface $variantGroup, array $groupData)
    {
        $variantGroupData = $this->filterVariantGroupData($groupData, true);
        $variantGroup = $this->denormalizer->denormalize(
            $variantGroupData,
            $this->class,
            $this->format,
            ['entity' => $variantGroup]
        );

        return $variantGroup;
    }

    /**
     * Update the variant group values
     *
     * @param GroupInterface $variantGroup
     * @param array          $groupData
     */
    protected function updateVariantGroupValues(GroupInterface $variantGroup, array $groupData)
    {
        $valuesData = $this->filterVariantGroupData($groupData, false);
        if (!empty($valuesData)) {
            $values = $this->denormalizeValuesFromItemData($valuesData);
            $this->validateValues($variantGroup, $values, $groupData);
            $template = $this->getProductTemplate($variantGroup);
            $template->setValues($values);
            $this->templateMediaManager->handleProductTemplateMedia($template);
            $structuredValuesData = $this->normalizeValuesToStructuredData($template->getValues());
            $template->setValuesData($structuredValuesData);
        }
    }

    /**
     * @param GroupInterface $variantGroup
     * @param array          $groupData
     *
     * @throws InvalidItemException
     */
    protected function validateVariantGroup(GroupInterface $variantGroup, array $groupData)
    {
        $violations = $this->validator->validate($variantGroup);
        if ($violations->count() !== 0) {
            $this->detachObject($variantGroup);
            $this->skipItemWithConstraintViolations($groupData, $violations);
        }
    }

    /**
     * Filters the item data to keep only variant group fields (code, axis, labels) or template product values
     *
     * @param array $groupData
     * @param bool  $keepOnlyFields if true keep only code, axis, labels, else keep only values
     *
     * @return array
     */
    protected function filterVariantGroupData(array $groupData, $keepOnlyFields = true)
    {
        foreach (array_keys($groupData) as $field) {
            $isCodeOrAxis = in_array($field, [self::CODE_FIELD, self::TYPE_FIELD, self::AXIS_FIELD]);
            $isLabel = 0 === strpos($field, self::LABEL_PATTERN);
            if ($keepOnlyFields && !$isCodeOrAxis && !$isLabel) {
                unset($groupData[$field]);
            } elseif (!$keepOnlyFields && ($isCodeOrAxis || $isLabel)) {
                unset($groupData[$field]);
            }
        }

        return $groupData;
    }

    /**
     * @param GroupInterface  $variantGroup
     * @param ArrayCollection $values       Collection of ProductValueInterface
     * @param array           $groupData
     *
     * @throw InvalidItemException
     */
    protected function validateValues(GroupInterface $variantGroup, ArrayCollection $values, array $groupData)
    {
        foreach ($values as $value) {
            $violations = $this->validator->validate($value);
            if ($violations->count() !== 0) {
                $this->detachObject($variantGroup);
                $this->skipItemWithConstraintViolations($groupData, $violations);
            }
        }
    }

    /**
     * Filter empty values then denormalize the product values objects from CSV fields
     *
     * @param array $rawProductValues
     *
     * @return ProductValueInterface[]
     */
    protected function denormalizeValuesFromItemData(array $rawProductValues)
    {
        $nonEmptyValues = $rawProductValues;
        foreach ($nonEmptyValues as $index => $data) {
            if (trim($data) === "") {
                unset($nonEmptyValues[$index]);
            }
        }

        return $this->denormalizer->denormalize($nonEmptyValues, 'ProductValue[]', 'csv');
    }

    /**
     * Normalize product value objects to JSON format
     *
     * @param ArrayCollection $values Collection of ProductValueInterface
     *
     * @return array
     */
    protected function normalizeValuesToStructuredData(ArrayCollection $values)
    {
        return $this->normalizer->normalize($values, 'json', ['entity' => 'product']);
    }

    /**
     * @param GroupInterface $variantGroup
     *
     * @return \Pim\Bundle\CatalogBundle\Model\ProductTemplateInterface
     */
    protected function getProductTemplate(GroupInterface $variantGroup)
    {
        if ($variantGroup->getProductTemplate()) {
            $template = $variantGroup->getProductTemplate();
        } else {
            $template = new $this->templateClass();
            $variantGroup->setProductTemplate($template);
        }

        return $template;
    }

    /**
     * Detaches the object from the unit of work
     *
     * Detach an object from the UOW is the responsibility of the writer, but to do so, it should know the
     * skipped items or we should use an explicit persist strategy
     *
     * @param mixed $object
     */
    protected function detachObject($object)
    {
        $this->detacher->detach($object);
    }
}
