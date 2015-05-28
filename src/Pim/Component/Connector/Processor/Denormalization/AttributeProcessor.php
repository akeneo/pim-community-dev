<?php

namespace Pim\Component\Connector\Processor\Denormalization;

use Akeneo\Bundle\StorageUtilsBundle\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Pim\Bundle\BaseConnectorBundle\Processor\Denormalization\AbstractProcessor;
use Pim\Bundle\CatalogBundle\Factory\AttributeFactory;
use Pim\Bundle\CatalogBundle\Model\attributeInterface;
use Pim\Component\Connector\ArrayConverter\StandardArrayConverterInterface;
use Pim\Component\ReferenceData\ConfigurationRegistryInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\ValidatorInterface;

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

    /**  @var ObjectDetacherInterface  */
    protected $detacher;

    /** @var ConfigurationRegistryInterface */
    protected $registry;

    /** @var array */
    protected $referenceDataType;

    /**
     * @param StandardArrayConverterInterface       $arrayConverter
     * @param IdentifiableObjectRepositoryInterface $repository
     * @param AttributeFactory                      $attributeFactory
     * @param ObjectUpdaterInterface                $updater
     * @param ValidatorInterface                    $validator
     * @param ObjectDetacherInterface               $detacher
     * @param ConfigurationRegistryInterface        $registry
     * @param array                                 $referenceDataType
     */
    public function __construct(
        StandardArrayConverterInterface $arrayConverter,
        IdentifiableObjectRepositoryInterface $repository,
        AttributeFactory $attributeFactory,
        ObjectUpdaterInterface $updater,
        ValidatorInterface $validator,
        ObjectDetacherInterface $detacher,
        ConfigurationRegistryInterface $registry,
        array $referenceDataType
    ) {
        parent::__construct($repository);
        $this->arrayConverter    = $arrayConverter;
        $this->attributeFactory  = $attributeFactory;
        $this->updater           = $updater;
        $this->validator         = $validator;
        $this->detacher          = $detacher;
        $this->registry          = $registry;
        $this->referenceDataType = $referenceDataType;
    }

    /**
     * {@inheritdoc}
     */
    public function process($item)
    {
        $convertedItem = $this->convertItemData($item);
        $this->checkIfReferenceDataExists($convertedItem);
        $attribute = $this->findOrCreateAttribute($convertedItem);

        try {
            $this->updateAttribute($attribute, $convertedItem);
        } catch (\InvalidArgumentException $exception) {
            $this->skipItemWithMessage($item, $exception->getMessage(), $exception);
        }

        $violations = $this->validateAttribute($attribute);
        if ($violations->count() > 0) {
            $this->detacher->detach($attribute);
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

    /**
     * @param string $value
     *
     * @return null
     */
    protected function checkIfReferenceDataExists($value)
    {
        if (isset($value['reference_data_name'])
            && (in_array($value['attributeType'], $this->referenceDataType))) {
            if (!$this->registry->has($value['reference_data_name'])) {
                $references = array_keys($this->registry->all());
                throw new \InvalidArgumentException(
                    sprintf(
                        'Reference data "%s" does not exist. Values allowed are: %s',
                        $value['reference_data_name'],
                        implode(', ', $references)
                    )
                );
            }
        }

        return null;
    }
}
