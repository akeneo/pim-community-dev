<?php

namespace Oro\Bundle\WorkflowBundle\Serializer\Normalizer;

use Symfony\Component\Serializer\Normalizer\SerializerAwareNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManager;

use Oro\Bundle\WorkflowBundle\Exception\WorkflowException;
use Oro\Bundle\WorkflowBundle\Serializer\WorkflowAwareSerializer;
use Oro\Bundle\WorkflowBundle\Model\StepAttribute;
use Oro\Bundle\WorkflowBundle\Model\Workflow;

/**
 * @TODO Cover with unit tests
 */
class WorkflowDataNormalizer extends SerializerAwareNormalizer implements NormalizerInterface, DenormalizerInterface
{
    /**
     * Local cache for StepAttributes of Workflow
     *
     * @var Collection[]
     */
    protected $stepAttributesByWorkflow = array();

    /**
     * @var array
     */
    protected $callbacks = array();

    /**
     * @var array
     */
    protected $ignoredAttributes = array();

    /**
     * @var ManagerRegistry
     */
    protected $registry;

    /**
     * @param ManagerRegistry $em
     */
    public function __construct(ManagerRegistry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * Set normalization callbacks
     *
     * @param array $callbacks help normalize the result
     * @throws InvalidArgumentException if a non-callable callback is set
     */
    public function setCallbacks(array $callbacks)
    {
        foreach ($callbacks as $attribute => $callback) {
            if (!is_callable($callback)) {
                throw new InvalidArgumentException(
                    sprintf('The given callback for attribute "%s" is not callable.', $attribute)
                );
            }
        }
        $this->callbacks = $callbacks;
    }

    /**
     * Set ignored attributes for normalization
     *
     * @param array $ignoredAttributes
     */
    public function setIgnoredAttributes(array $ignoredAttributes)
    {
        $this->ignoredAttributes = $ignoredAttributes;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = array())
    {
        $attributes = array();
        foreach ($object as $attributeName => $attributeValue) {
            if (in_array($attributeName, $this->ignoredAttributes)) {
                continue;
            }

            if (array_key_exists($attributeName, $this->callbacks)) {
                $attributeValue = call_user_func($this->callbacks[$attributeName], $attributeValue);
            }

            $stepAttribute = $this->getStepAttributes()->get($attributeName);
            if ($stepAttribute) {
                $attributeValue = $this->normalizeStepAttribute($stepAttribute, $attributeValue);
            }

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
        $object = new $class;

        foreach ($data as $attributeName => $attributeValue) {
            $stepAttribute = $this->getStepAttributes()->get($attributeName);

            if ($stepAttribute) {
                $attributeValue = $this->denormalizeStepAttribute($stepAttribute, $attributeValue);
            }

            $object->set($attributeName, $attributeValue);
        }

        return $object;
    }

    /**
     * Normalizes a value of StepAttribute into a scalar
     *
     * @param StepAttribute $stepAttribute
     * @param mixed $attributeValue
     * @return mixed
     */
    public function normalizeStepAttribute(StepAttribute $stepAttribute, $attributeValue)
    {
        // @TODO Check is attribute is an entity and return it's id
        return $attributeValue;
    }

    /**
     * Denormalizes value of StepAttrubute back into it's model representation
     *
     * @param StepAttribute $stepAttribute
     * @param mixed $attributeValue
     * @return mixed
     */
    public function denormalizeStepAttribute(StepAttribute $stepAttribute, $attributeValue)
    {
        // @TODO Check is attribute is an entity and convert it's id to object
        return $attributeValue;
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
        $WorkflowDataClass = 'Oro\Bundle\WorkflowBundle\Model\WorkflowData';
        return $WorkflowDataClass == $class || in_array($WorkflowDataClass, class_parents($class));
    }

    /**
     * Get collection of StepAttributes for current Workflow
     *
     * @return Collection
     * @throws WorkflowException
     */
    protected function getStepAttributes()
    {
        $workflow = $this->getWorkflow();
        $workflowName = $workflow->getName();
        if (!isset($this->stepAttributesByWorkflow[$workflowName])) {
            $this->stepAttributesByWorkflow[$workflowName] = $workflow->getStepAttributes();
        }
        return $this->stepAttributesByWorkflow[$workflowName];
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
