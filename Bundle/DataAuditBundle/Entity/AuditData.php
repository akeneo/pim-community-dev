<?php

namespace Oro\Bundle\DataAuditBundle\Entity;

use BeSimple\SoapBundle\ServiceDefinition\Annotation as Soap;
use BeSimple\SoapCommon\Type\AbstractKeyValue;

class AuditData extends AbstractKeyValue
{
    /**
     * @Soap\ComplexType("string")
     */
    public $key;

    /**
     * @Soap\ComplexType("Oro\Bundle\SoapBundle\Type\KeyValue\String[]", nillable=true)
     */
    protected $value;

    public function __construct($key, $value)
    {
        $this->key   = $key;
        $this->value = $value;
    }
}
