<?php

namespace Pim\Bundle\BaseConnectorBundle\Processor;

use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Akeneo\Bundle\BatchBundle\Item\AbstractConfigurableStepElement;
use Akeneo\Bundle\BatchBundle\Item\InvalidItemException;
use Akeneo\Bundle\BatchBundle\Item\ItemProcessorInterface;
use Akeneo\Bundle\BatchBundle\Step\StepExecutionAwareInterface;
use Pim\Bundle\CatalogBundle\Entity\Repository\GroupRepository;
use Pim\Bundle\CatalogBundle\Model\GroupInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\ValidatorInterface;

/**
 * Variant group values import processor, allows to bind values data into a product template linked to a variant group
 * and validate values, it erases existing values
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class VariantGroupValuesProcessor extends AbstractConfigurableStepElement implements
    ItemProcessorInterface,
    StepExecutionAwareInterface
{
    /** @staticvar string */
    const VARIANT_GROUP_CODE_FIELD = 'variant_group_code';

    /** @var StepExecution */
    protected $stepExecution;

    /** @var GroupRepository */
    protected $groupRepository;

    /** @var DenormalizerInterface */
    protected $groupValuesDenormalizer;

    /** @var ValidatorInterface */
    protected $validator;

    /** @var NormalizerInterface */
    protected $valueNormalizer;

    /** @var string */
    protected $templateClass;

    /**
     * @param GroupRepository       $groupRepository
     * @param DenormalizerInterface $groupValuesDenormalizer
     * @param ValidatorInterface    $validator
     * @param NormalizerInterface   $valueNormalizer
     * @param string                $templateClass
     */
    public function __construct(
        GroupRepository $groupRepository,
        DenormalizerInterface $groupValuesDenormalizer,
        ValidatorInterface $validator,
        NormalizerInterface $valueNormalizer,
        $templateClass
    ) {
        $this->groupRepository         = $groupRepository;
        $this->groupValuesDenormalizer = $groupValuesDenormalizer;
        $this->validator               = $validator;
        $this->valueNormalizer         = $valueNormalizer;
        $this->templateClass           = $templateClass;
    }

    /**
     * {@inheritdoc}
     */
    public function process($item)
    {
        // extract values from raw data (csv) to validate them
        $variantGroup = $this->getVariantGroup($item);
        $itemValuesData = $this->cleanItemData($item);
        $values = $this->denormalizeValuesFromItemData($itemValuesData);
        $this->validateValues($values, $item);

        // store values as product template format (json)
        $template = $this->getProductTemplate($variantGroup);
        $structuredValuesData = $this->normalizeValuesToStructuredData($values);
        $template->setValuesData($structuredValuesData);
        $this->validateVariantGroup($variantGroup, $item);

        return $variantGroup;
    }

    /**
     * {@inheritdoc}
     */
    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFields()
    {
        return [];
    }

    /**
     * @param array $item
     *
     * @return GroupInterface
     *
     * @throws InvalidItemException
     */
    protected function getVariantGroup($item)
    {
        if (!isset($item[self::VARIANT_GROUP_CODE_FIELD])) {
            throw new \LogicException('Variant group code must be provided');
        }

        $variantGroupCode = $item[self::VARIANT_GROUP_CODE_FIELD];
        $variantGroup = $this->groupRepository->findOneByCode($variantGroupCode);
        if (!$variantGroup || !$variantGroup->getType()->isVariant()) {
            $this->stepExecution->incrementSummaryInfo('skip');
            throw new InvalidItemException(
                sprintf('Variant group "%s" does not exist', $variantGroupCode),
                $item
            );
        }

        return $variantGroup;
    }

    /**
     * Prepare value raw data
     *
     * @param array $item
     *
     * @return array
     */
    protected function cleanItemData($item)
    {
        unset($item[self::VARIANT_GROUP_CODE_FIELD]);

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
        return $this->groupValuesDenormalizer->denormalize($rawProductValues, 'variant_group_values', 'csv');
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
