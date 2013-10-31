<?php

namespace Oro\Bundle\EmailBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Doctrine\Common\Collections\Collection;

class EmailAddressTransformer implements DataTransformerInterface
{
    /**
     * @var bool
     */
    protected $multiple;

    /**
     * Constructor
     *
     * @param bool $multiple
     */
    public function __construct($multiple = false)
    {
        $this->multiple = $multiple;
    }

    /**
     * Transforms a list of email addresses (if $this->multiple == true) or
     * an email address (if $this->multiple == false) to a string
     *
     * {@inheritdoc}
     */
    public function transform($value)
    {
        if (!$value) {
            return '';
        }

        if ($this->multiple) {
            if (is_string($value)) {
                $value = explode(';', $value);
            }
            if ($value instanceof Collection) {
                $value = $value->toArray();
            }
            $result = implode('; ', array_filter(array_map('trim', $value)));
        } else {
            $result = trim($value);
        }

        return $result;
    }

    /**
     * Transforms a string to a list of email addresses (if $this->multiple == true) or
     * an email address (if $this->multiple == false)
     *
     * {@inheritdoc}
     */
    public function reverseTransform($value)
    {
        if (!$value) {
            return $this->multiple
                ? array()
                : null;
        }

        if ($this->multiple) {
            $result = array_values(array_filter(array_map('trim', explode(';', $value))));
        } else {
            $result = trim($value);
            if (empty($result)) {
                $result = null;
            }
        }

        return $result;
    }
}
