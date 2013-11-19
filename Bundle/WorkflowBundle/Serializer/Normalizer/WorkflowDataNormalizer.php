<?php

namespace Oro\Bundle\WorkflowBundle\Serializer\Normalizer;

use Symfony\Component\Serializer\Normalizer\SerializerAwareNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

use Oro\Bundle\WorkflowBundle\Exception\SerializerException;
use Oro\Bundle\WorkflowBundle\Serializer\WorkflowAwareSerializer;
use Oro\Bundle\WorkflowBundle\Model\Attribute;
use Oro\Bundle\WorkflowBundle\Model\Workflow;

class WorkflowDataNormalizer extends SerializerAwareNormalizer implements NormalizerInterface, DenormalizerInterface
{
    /**
     * @var AttributeNormalizer[]
     */
    protected $attributeNormalizers;

    /**
     * @param AttributeNormalizer $attributeNormalizer
     */
    public function addAttributeNormalizer(AttributeNormalizer $attributeNormalizer)
    {
        $this->attributeNormalizers[] = $attributeNormalizer;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = array())
    {
        $attributes = array();
        $workflow = $this->getWorkflow();
        foreach ($object as $attributeName => $attributeValue) {
            $attribute = $this->getAttribute($workflow, $attributeName);
            $attributeValue = $this->normalizeAttribute($workflow, $attribute, $attributeValue);

            if (null !== $attributeValue &&
                !is_scalar($attributeValue) &&
                $this->serializer instanceof NormalizerInterface
            ) {
                $attributeValue = $this->serializer->normalize($attributeValue, $format);
            }
            $attributes[$attributeName] = $attributeValue;
        }

        return $attributes;
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = array())
    {
        $denormalizedData = array();
        $workflow = $this->getWorkflow();

        foreach ($data as $attributeName => $attributeValue) {
            $attribute = $this->getAttribute($workflow, $attributeName);
            $attributeValue = $this->denormalizeAttribute($workflow, $attribute, $attributeValue);
            $denormalizedData[$attributeName] = $attributeValue;
        }

        return $object = new $class($denormalizedData);
    }

    /**
     * @param Workflow $workflow
     * @param string $attributeName
     * @return \Oro\Bundle\WorkflowBundle\Model\Attribute
     * @throws SerializerException If attribute not found
     */
    protected function getAttribute(Workflow $workflow, $attributeName)
    {
        $attribute = $workflow->getAttributeManager()->getAttribute($attributeName);
        if (!$attribute) {
            throw new SerializerException(
                sprintf(
                    'Workflow "%s" has no attribute "%s"',
                    $workflow->getName(),
                    $attributeName
                )
            );
        }
        return $attribute;
    }

    /**
     * @param Workflow $workflow
     * @param Attribute $attribute
     * @param mixed $attributeValue
     * @return mixed
     */
    protected function normalizeAttribute(Workflow $workflow, Attribute $attribute, $attributeValue)
    {
        $normalizer = $this->findAttributeNormalizer('normalization', $workflow, $attribute, $attributeValue);
        return $normalizer->normalize($workflow, $attribute, $attributeValue);
    }

    /**
     * @param Workflow $workflow
     * @param Attribute $attribute
     * @param mixed $attributeValue
     * @return AttributeNormalizer
     * @throws SerializerException
     */
    protected function denormalizeAttribute(Workflow $workflow, Attribute $attribute, $attributeValue)
    {
        $normalizer = $this->findAttributeNormalizer('denormalization', $workflow, $attribute, $attributeValue);
        return $normalizer->denormalize($workflow, $attribute, $attributeValue);
    }

    /**
     * @param string $direction
     * @param Workflow $workflow
     * @param Attribute $attribute
     * @param mixed $attributeValue
     * @return AttributeNormalizer
     * @throws SerializerException
     */
    protected function findAttributeNormalizer($direction, Workflow $workflow, Attribute $attribute, $attributeValue)
    {
        $method = 'supports' . ucfirst($direction);
        foreach ($this->attributeNormalizers as $normalizer) {
            if ($normalizer->$method($workflow, $attribute, $attributeValue)) {
                return $normalizer;
            }
        }
        throw new SerializerException(
            sprintf(
                'Cannot handle "%s" of attribute "%s" of workflow "%s"',
                $direction,
                $attribute->getName(),
                $workflow->getName()
            )
        );
    }

    /**
     * {@inheritDoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return is_object($data) && $this->supportsClass(get_class($data));
    }

    /**
     * {@inheritDoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return $this->supportsClass($type);
    }

    /**
     * Checks if the given class is WorkflowData or it's ancestor.
     *
     * @param string $class
     * @return Boolean
     */
    protected function supportsClass($class)
    {
        $workflowDataClass = 'Oro\Bundle\WorkflowBundle\Model\WorkflowData';
        return $workflowDataClass == $class
            || (is_string($class) && class_exists($class) && in_array($workflowDataClass, class_parents($class)));
    }

    /**
     * Get Workflow
     *
     * @return Workflow
     * @throws SerializerException
     */
    protected function getWorkflow()
    {
        if (!$this->serializer instanceof WorkflowAwareSerializer) {
            throw new SerializerException(
                sprintf(
                    'Cannot get Workflow. Serializer must implement %s',
                    'Oro\Bundle\WorkflowBundle\Serializer\WorkflowAwareSerializer'
                )
            );
        }
        return $this->serializer->getWorkflow();
    }
}
