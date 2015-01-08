<?php

namespace Pim\Bundle\BaseConnectorBundle\Processor\ArrayToObject\Flat;

use Akeneo\Bundle\BatchBundle\Item\InvalidItemException;
use Pim\Bundle\BaseConnectorBundle\Processor\ArrayToObject\AbstractProcessor;
use Pim\Bundle\CatalogBundle\Model\GroupInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Pim\Bundle\CatalogBundle\Repository\ReferableEntityRepositoryInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
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
    const AXIS_FIELD = 'axis';

    /** @staticvar string */
    const LABEL_FIELD = 'label';

    /** @var ReferableEntityRepositoryInterface */
    protected $groupTypeRepository;

    /** @var NormalizerInterface */
    protected $valueNormalizer;

    /** @var string */
    protected $templateClass;

    /**
     * @param ReferableEntityRepositoryInterface $groupRepository
     * @param DenormalizerInterface              $groupValuesDenormalizer
     * @param ValidatorInterface                 $validator
     * @param NormalizerInterface                $valueNormalizer
     * @param string                             $groupClass
     * @param string                             $templateClass
     */
    public function __construct(
        ReferableEntityRepositoryInterface $groupRepository,
        DenormalizerInterface $groupValuesDenormalizer,
        ValidatorInterface $validator,
        NormalizerInterface $valueNormalizer,
        $groupClass,
        $templateClass
    ) {
        parent::__construct($groupRepository, $groupValuesDenormalizer, $validator, $groupClass);
        $this->valueNormalizer = $valueNormalizer;
        $this->templateClass   = $templateClass;
    }

    /**
     * {@inheritdoc}
     */
    public function process($item)
    {
        /** @var GroupInterface $variantGroup */
        $variantGroup = $this->findOrCreateObject($this->repository, $item, $this->class);
        $this->updateVariantGroup($variantGroup, $item);
        $this->updateVariantGroupValues($variantGroup, $item);
        $this->validateVariantGroup($variantGroup, $item);

        return $variantGroup;
    }

    /**
     * Update the variant group fields
     *
     * @param GroupInterface $variantGroup
     * @param array          $item
     *
     * @return GroupInterface
     * @throws InvalidItemException
     */
    protected function updateVariantGroup(GroupInterface $variantGroup, array $item)
    {
        if (null !== $variantGroup->getId() && !$variantGroup->getType()->isVariant()) {
            $this->stepExecution->incrementSummaryInfo('skip');
            throw new InvalidItemException(
                sprintf('Variant group "%s" does not exist', $item[self::CODE_FIELD]),
                $item
            );
        }
        $variantGroupData = $this->filterVariantGroupData($item, true);
        $variantGroupData['type'] = 'VARIANT';
        $variantGroup = $this->denormalizer->denormalize(
            $variantGroupData,
            $this->class,
            'csv',
            ['entity' => $variantGroup]
        );

        return $variantGroup;
    }

    /**
     * Update the variant group values
     *
     * @param GroupInterface $variantGroup
     * @param array          $item
     */
    protected function updateVariantGroupValues(GroupInterface $variantGroup, array $item)
    {
        $valuesData = $this->filterVariantGroupData($item, false);
        if (!empty($valuesData)) {
            $values = $this->denormalizeValuesFromItemData($valuesData);
            $this->validateValues($values, $item);
            $template = $this->getProductTemplate($variantGroup);
            $structuredValuesData = $this->normalizeValuesToStructuredData($values);
            $template->setValuesData($structuredValuesData);
        }
    }

    /**
     * Filters the item data to keep only variant group fields (code, axis, labels) or template product values
     *
     * @param array $item
     * @param bool  $keepOnlyFields if true keep only code, axis, labels, else keep only values
     *
     * @return array
     */
    protected function filterVariantGroupData(array $item, $keepOnlyFields = true)
    {
        foreach (array_keys($item) as $field) {
            $isCodeOrAxis = in_array($field, [self::CODE_FIELD, self::AXIS_FIELD]);
            $isLabel = false !== strpos($field, self::LABEL_FIELD, 0);
            if ($keepOnlyFields && !$isCodeOrAxis && !$isLabel) {
                unset($item[$field]);
            } elseif (!$keepOnlyFields && ($isCodeOrAxis || $isLabel)) {
                unset($item[$field]);
            }
        }

        return $item;
    }

    /**
     * Prepare product value objects from CSV fields
     *
     * @param array $rawProductValues
     *
     * @return ProductValueInterface[]
     */
    protected function denormalizeValuesFromItemData(array $rawProductValues)
    {
        return $this->denormalizer->denormalize($rawProductValues, 'ProductValue[]', 'csv');
    }

    /**
     * @param ProductValueInterface[] $values
     * @param array                   $item
     *
     * @throw InvalidItemException
     */
    protected function validateValues(array $values, array $item)
    {
        foreach ($values as $value) {
            $violations = $this->validator->validate($value);
            if ($violations->count() !== 0) {
                $this->skipItem($item, $violations);
            }
        }
    }

    /**
     * @param GroupInterface $variantGroup
     * @param array          $item
     *
     * @throws InvalidItemException
     */
    protected function validateVariantGroup(GroupInterface $variantGroup, array $item)
    {
        $violations = $this->validator->validate($variantGroup);
        if ($violations->count() !== 0) {
            $this->skipItem($item, $violations);
        }
    }

    /**
     * @param ProductValueInterface[] $values
     *
     * @return array
     */
    protected function normalizeValuesToStructuredData(array $values)
    {
        $normalizedValues = [];

        foreach ($values as $value) {
            $normalizedValues[$value->getAttribute()->getCode()][] = $this->valueNormalizer->normalize($value, 'json', ['entity' => 'product']);
        }

        return $normalizedValues;
    }

    /**
     * @param array                            $item
     * @param ConstraintViolationListInterface $violations
     *
     * @throws InvalidItemException
     */
    protected function skipItem($item, ConstraintViolationListInterface $violations)
    {
        $this->stepExecution->incrementSummaryInfo('skip');

        // TODO detach when skip ?

        $messages = [];
        foreach ($violations as $violation) {
            $messages[] = sprintf(
                "%s: %s",
                $violation->getMessage(),
                $violation->getInvalidValue()
            );
        }

        throw new InvalidItemException(implode(', ', $messages), $item);
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
}
