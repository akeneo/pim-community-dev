<?php

namespace Oro\Bundle\WorkflowBundle\Model\Pass;

use Symfony\Component\PropertyAccess\PropertyPath;

/**
 * Passes through configuration array and replaces parameter strings ($parameter.name)
 * with appropriate PropertyPath objects
 */
class ParameterPass implements PassInterface
{
    /**
     * @var string
     */
    protected $prefix;

    /**
     * @param string|null $prefix
     */
    public function __construct($prefix = null)
    {
        $this->prefix = $prefix;
    }

    /**
     * {@inheritDoc}
     */
    public function pass(array $data)
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = $this->pass($value);
            } elseif (is_string($value) && $this->isParameter($value)) {
                $data[$key] = $this->convertParameterToPropertyPath($value);
            }
        }

        return $data;
    }

    /**
     * @param string $string
     * @return bool
     */
    protected function isParameter($string)
    {
        return strpos($string, '$') === 0;
    }

    /**
     * @param string $string
     * @return PropertyPath
     */
    protected function convertParameterToPropertyPath($string)
    {
        $property = substr($string, 1);

        if (0 === strpos($property, '.')) {
            $property = substr($property, 1);
        } elseif ($this->prefix) {
            $property = $this->prefix . '.' .  $property;
        }

        return new PropertyPath($property);
    }
}
