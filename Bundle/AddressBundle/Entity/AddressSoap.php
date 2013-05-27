<?php

namespace Oro\Bundle\AddressBundle\Entity;

use BeSimple\SoapBundle\ServiceDefinition\Annotation as Soap;
use JMS\Serializer\Annotation\Exclude;

class AddressSoap extends Address
{
    /**
     * @Exclude
     */
    protected $values;

    /**
     * @Soap\ComplexType("Oro\Bundle\SoapBundle\Entity\FlexibleAttribute[]", nillable=true)
     */
    protected $attributes;
}
