<?php

namespace Oro\Bundle\WorkflowBundle\Serializer\Normalizer;

use Oro\Bundle\WorkflowBundle\Exception\SerializeWorkflowDataException;
use Symfony\Component\Serializer\Normalizer\SerializerAwareNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

use Oro\Bundle\WorkflowBundle\Exception\WorkflowException;
use Oro\Bundle\WorkflowBundle\Serializer\WorkflowAwareSerializer;
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
            $attributeValue = $this->normalizeAttribute($workflow, $attributeName, $attributeValue);

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
     * @param Workflow $workflow
     * @param string $attributeName
     * @param mixed $attributeValue
     * @return mixed
     */
    protected function normalizeAttribute(Workflow $workflow, $attributeName, $attributeValue)
    {
        $normalizer = $this->findAttributeNormalizer('normalization', $workflow, $attributeName, $attributeValue);
        return $normalizer->normalize($workflow, $attributeName, $attributeValue);
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = array())
    {
        $denormalizedData = array();
        $workflow = $this->getWorkflow();

        foreach ($data as $attributeName => $attributeValue) {
            $attributeValue = $this->denormalizeAttribute($workflow, $attributeName, $attributeValue);
            $denormalizedData[$attributeName] = $attributeValue;
        }

        return $object = new $class($denormalizedData);
    }

    /**
     * @param Workflow $workflow
     * @param string $attributeName
     * @param mixed $attributeValue
     * @return AttributeNormalizer
     */
    protected function denormalizeAttribute(Workflow $workflow, $attributeName, $attributeValue)
    {
        $normalizer = $this->findAttributeNormalizer('denormalization', $workflow, $attributeName, $attributeValue);
        return $normalizer->denormalize($workflow, $attributeName, $attributeValue);
    }

    /**
     * @param string $direction
     * @param Workflow $workflow
     * @param string $attributeName
     * @param mixed $attributeValue
     * @return AttributeNormalizer
     * @throws SerializeWorkflowDataException
     */
    protected function findAttributeNormalizer($direction, Workflow $workflow, $attributeName, $attributeValue)
    {
        $method = 'supports' . ucfirst($direction);
        foreach ($this->attributeNormalizers as $normalizer) {
            if ($normalizer->$method($workflow, $attributeName, $attributeValue)) {
                return $normalizer;
            }
        }
        throw new SerializeWorkflowDataException(
            sprintf(
                'Cannot handle "%s" of attribute "%s" of workflow "%s"',
                $direction,
                $attributeName,
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
        return $workflowDataClass == $class || in_array($workflowDataClass, class_parents($class));
    }

    /**
     * Get Workflow
     *
     * @return Workflow
     * @throws WorkflowException
     */
    protected function getWorkflow()
    {
        if (!$this->serializer instanceof WorkflowAwareSerializer) {
            throw new WorkflowException(
                sprintf(
                    'Cannot get Workflow. Serializer must implement %s',
                    'Oro\Bundle\WorkflowBundle\Serializer\WorkflowAwareSerializer'
                )
            );
        }
        return $this->serializer->getWorkflow();
    }
}
