<?php

namespace Oro\Bundle\ImapBundle\Extensions\Zend\Mail\Storage;

class Value
{
    /**
     * @var string
     */
    private $value;

    /**
     * @var string
     */
    private $encoding;

    /**
     * @param string $value
     * @param string $encoding
     */
    public function __construct($value, $encoding)
    {
        $this->value = $value;
        $this->encoding = $encoding;
    }

    /**
     * Gets the value
     *
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Gets the value encoding
     *
     * @return string
     */
    public function getEncoding()
    {
        return $this->encoding;
    }
}
