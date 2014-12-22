<?php

namespace Pim\Bundle\BaseConnectorBundle\Processor;

use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Akeneo\Bundle\BatchBundle\Item\AbstractConfigurableStepElement;
use Akeneo\Bundle\BatchBundle\Item\InvalidItemException;
use Akeneo\Bundle\BatchBundle\Item\ItemProcessorInterface;
use Akeneo\Bundle\BatchBundle\Step\StepExecutionAwareInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Pim\Bundle\CatalogBundle\Entity\ProductTemplate;
use Pim\Bundle\CatalogBundle\Entity\Repository\GroupRepository;
use Pim\Bundle\CatalogBundle\Model\GroupInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Pim\Bundle\CatalogBundle\Util\ProductValueKeyGenerator;
use Pim\Bundle\TransformBundle\Builder\FieldNameBuilder;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Validator\ConstraintViolationList;
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
    protected $valueDenormalizer;

    /** @var FieldNameBuilder */
    protected $fieldNameBuilder;

    /** @var ValidatorInterface */
    protected $valueValidator;

    /**
     * @param GroupRepository       $groupRepository
     * @param DenormalizerInterface $valueDenormalizer
     * @param FieldNameBuilder      $fieldNameBuilder
     * @param ValidatorInterface    $valueValidator
     */
    public function __construct(
        GroupRepository $groupRepository,
        DenormalizerInterface $valueDenormalizer,
        FieldNameBuilder $fieldNameBuilder,
        ValidatorInterface $valueValidator
    ) {
        $this->groupRepository   = $groupRepository;
        $this->valueDenormalizer = $valueDenormalizer;
        $this->fieldNameBuilder  = $fieldNameBuilder;
        $this->valueValidator    = $valueValidator;
    }

    /**
     * {@inheritdoc}
     */
    public function process($item)
    {
        $variantGroup = $this->getVariantGroup($item);
        $valueData = $this->prepareValuesData($item);
        $values = $this->prepareValues($valueData);
        $this->validateValues($values, $item);
        $template = $this->getProductTemplate($variantGroup);
        $template->setValuesData($valueData); // TODO : will be replaced by json

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
            $this->stepExecution->incrementSummaryInfo('skip');
            throw new InvalidItemException("Variant group code must be provided", $item);
        }

        $variantGroup = $this->groupRepository->findOneByCode($item[self::VARIANT_GROUP_CODE_FIELD]);
        if (!$variantGroup || !$variantGroup->getType()->isVariant()) {
            $this->stepExecution->incrementSummaryInfo('skip');
            throw new InvalidItemException("Variant group doesn't exist", $item);
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
    protected function prepareValuesData($item)
    {
        unset($item[self::VARIANT_GROUP_CODE_FIELD]);

        return $item;
    }

    /**
     * Prepare product value objects
     *
     * @param array $rawProductValues
     *
     * @return ProductValueInterface[]
     */
    protected function prepareValues(array $rawProductValues)
    {
        // TODO : inject class
        $productValueClass = 'Pim\Bundle\CatalogBundle\Model\ProductValue';
        $productValues = [];

        foreach ($rawProductValues as $attFieldName => $dataValue) {
            $attributeInfos = $this->fieldNameBuilder->extractAttributeFieldNameInfos($attFieldName);
            $attribute = $attributeInfos['attribute'];
            $value = new $productValueClass();
            $value->setAttribute($attribute);
            $value->setLocale($attributeInfos['locale_code']);
            $value->setScope($attributeInfos['scope_code']);
            unset($attributeInfos['attribute']);
            unset($attributeInfos['locale_code']);
            unset($attributeInfos['scope_code']);

            $productValues[] = $this->valueDenormalizer->denormalize(
                $dataValue,
                $productValueClass,
                'csv',
                ['entity' => $value] + $attributeInfos
            );
        }

        return $productValues;
    }

    /**
     * @param ProductValueInterface[] $values
     * @param array                   $item
     *
     * @throw InvalidItemException
     */
    protected function validateValues(array $values, $item)
    {
        foreach ($values as $value) {
            $violations = $this->valueValidator->validate($value);
            if ($violations->count() !== 0) {
                $this->skipItem($item, $violations);
            }
        }
    }

    /**
     * @param array                            $item
     * @param ConstraintViolationListInterface $violations
     *
     * @throws InvalidItemException
     */
    protected function skipItem($item, ConstraintViolationListInterface $violations)
    {
        // TODO detach when skip
        $messages = [];
        foreach ($violations as $violation) {
            $messages[] = sprintf(
                "%s : %s",
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
            // TODO inject FQCN or Factory
            $template = new ProductTemplate();
            $variantGroup->setProductTemplate($template);
        }

        return $template;
    }
}
