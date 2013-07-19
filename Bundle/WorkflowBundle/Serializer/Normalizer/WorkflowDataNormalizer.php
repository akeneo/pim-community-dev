<?php

namespace Oro\Bundle\WorkflowBundle\Serializer\Normalizer;

use Symfony\Component\Serializer\Normalizer\SerializerAwareNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManager;

class WorkflowDataNormalizer extends SerializerAwareNormalizer implements NormalizerInterface, DenormalizerInterface
{
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
     *
     * @TODO Cover with unit tests
     * @TODO Use Workflow StepAttributes to normalize values, use EntityManager to normalize entities.
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
     *
     * @TODO Use Workflow StepAttributes to normalize values, use EntityManager to normalize entities.
     */
    public function denormalize($data, $class, $format = null, array $context = array())
    {
        $object = new $class;

        foreach ($data as $attribute => $value) {
            $object->set($attribute, $value);
        }

        return $object;
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
}
