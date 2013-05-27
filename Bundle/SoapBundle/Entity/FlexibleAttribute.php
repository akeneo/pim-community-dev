<?php

namespace Oro\Bundle\SoapBundle\Entity;

use BeSimple\SoapBundle\ServiceDefinition\Annotation as Soap;

class FlexibleAttribute
{
    /**
     * @Soap\ComplexType("string")
     */
    public $code;

    /**
     * @Soap\ComplexType("string")
     */
    public $value;

    public function __construct($code, $value)
    {
        $this->code  = $code;
        $this->value = $value;
    }
}
