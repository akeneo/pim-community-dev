<?php

namespace Oro\Bundle\WorkflowBundle\Serializer\Normalizer;

use Symfony\Component\Serializer\Normalizer\SerializerAwareNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

use Oro\Bundle\WorkflowBundle\Exception\WorkflowException;
use Oro\Bundle\WorkflowBundle\Serializer\WorkflowAwareSerializer;
use Oro\Bundle\WorkflowBundle\Model\Workflow;

class WorkflowDataNormalizer extends SerializerAwareNormalizer implements NormalizerInterface, DenormalizerInterface
{
    /**
     * @var AttributeNormalizer
     */
    protected $attributeNormalizer;

    /**
     * @param AttributeNormalizer $attributeNormalizer
     */
    public function __construct(AttributeNormalizer $attributeNormalizer)
    {
        $this->attributeNormalizer = $attributeNormalizer;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = array())
    {
        $attributes = array();
        foreach ($object as $attributeName => $attributeValue) {
            $attributeValue =
                $this->attributeNormalizer->normalize($this->getWorkflow(), $attributeName, $attributeValue);

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

        foreach ($data as $attributeName => $attributeValue) {
            $attributeValue =
                $this->attributeNormalizer->denormalize($this->getWorkflow(), $attributeName, $attributeValue);

            $denormalizedData[$attributeName] = $attributeValue;
        }

        return $object = new $class($denormalizedData);
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
