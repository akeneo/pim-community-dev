<?php

namespace Oro\Bundle\SoapBundle\Type\KeyValue;

use BeSimple\SoapBundle\ServiceDefinition\Annotation as Soap;
use BeSimple\SoapCommon\Type\KeyValue\String as KeyValueString;

class String extends KeyValueString
{
    /**
     * @Soap\ComplexType("string", nillable=true)
     */
    protected $value;
}
