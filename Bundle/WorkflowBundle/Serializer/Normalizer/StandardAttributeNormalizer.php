<?php

namespace Oro\Bundle\WorkflowBundle\Serializer\Normalizer;

use Oro\Bundle\WorkflowBundle\Model\Attribute;
use Oro\Bundle\WorkflowBundle\Model\Workflow;

class StandardAttributeNormalizer implements AttributeNormalizer
{
    protected $normalTypes = array(
        'string'  => 'string',
        'int'     => 'integer',
        'integer' => 'integer',
        'bool'    => 'boolean',
        'boolean' => 'boolean',
        'float'   => 'float',
        'array'   => 'array',
        'object'  => 'object',
    );

    /**
     * {@inheritdoc}
     */
    public function normalize(Workflow $workflow, Attribute $attribute, $attributeValue)
    {
        $normalType = $this->normalTypes[$attribute->getType()];
        $normalizeMethod = 'normalize' . ucfirst($normalType);
        return (null === $attributeValue) ? $attributeValue : $this->$normalizeMethod($attributeValue, $attribute);
    }

    /**
     * @param mixed $value
     * @return string|null
     */
    protected function normalizeString($value)
    {
        if (is_scalar($value) || is_object($value) && method_exists($value, '__toString')) {
            return (string)$value;
        }
        return null;
    }

    /**
     * @param mixed $value
     * @return int|null
     */
    protected function normalizeInteger($value)
    {
        return is_scalar($value) ? (int)$value : null;
    }

    /**
     * @param mixed $value
     * @return bool
     */
    protected function normalizeBoolean($value)
    {
        return (bool)$value;
    }

    /**
     * @param mixed $value
     * @return float|null
     */
    protected function normalizeFloat($value)
    {
        return is_scalar($value) ? (float)$value : null;
    }

    /**
     * @param mixed $value
     * @return string
     */
    protected function normalizeArray($value)
    {
        if (!is_array($value)) {
            $value = array();
        }
        return $this->serialize($value);
    }

    /**
     * @param mixed $value
     * @param Attribute $attribute
     * @return string
     */
    protected function normalizeObject($value, Attribute $attribute)
    {
        $class = $attribute->getOption('class');
        if (!is_object($value) || !$value instanceof $class) {
            return null;
        }
        return $this->serialize($value);
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize(Workflow $workflow, Attribute $attribute, $attributeValue)
    {
        $normalType = $this->normalTypes[$attribute->getType()];
        $denormalizeMethod = 'denormalize' . ucfirst($normalType);
        return (null === $attributeValue) ? $attributeValue : $this->$denormalizeMethod($attributeValue, $attribute);
    }

    /**
     * @param mixed $value
     * @return string|null
     */
    protected function denormalizeString($value)
    {
        return is_scalar($value) ? (string)$value : null;
    }

    /**
     * @param mixed $value
     * @return int|null
     */
    protected function denormalizeInteger($value)
    {
        return is_scalar($value) ? (int)$value : null;
    }

    /**
     * @param mixed $value
     * @return bool
     */
    protected function denormalizeBoolean($value)
    {
        return (bool)$value;
    }

    /**
     * @param mixed $value
     * @return float|null
     */
    protected function denormalizeFloat($value)
    {
        return is_scalar($value) ? (float)$value : null;
    }

    /**
     * @param mixed $value
     * @return array
     */
    protected function denormalizeArray($value)
    {
        if (!is_string($value)) {
            return array();
        }
        $value = $this->unserialize($value);
        if (!is_array($value)) {
            return array();
        }
        return $value;
    }

    /**
     * @param mixed $value
     * @param Attribute $attribute
     * @return object|null
     */
    protected function denormalizeObject($value, Attribute $attribute)
    {
        $value = $this->unserialize($value);
        $class = $attribute->getOption('class');
        if (!is_object($value) || !$value instanceof $class) {
            $value = null;
        }
        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization(Workflow $workflow, Attribute $attribute, $attributeValue)
    {
        return !empty($this->normalTypes[$attribute->getType()]);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization(Workflow $workflow, Attribute $attribute, $attributeValue)
    {
        return !empty($this->normalTypes[$attribute->getType()]);
    }

    /**
     * @param mixed $value
     * @return string
     */
    protected function serialize($value)
    {
        return base64_encode(serialize($value));
    }

    /**
     * @param string $value
     * @return mixed|null
     */
    protected function unserialize($value)
    {
        if (!is_string($value)) {
            return null;
        }
        $value = base64_decode($value);
        if (!is_string($value) || !$value) {
            return null;
        }
        return unserialize($value);
    }
}
