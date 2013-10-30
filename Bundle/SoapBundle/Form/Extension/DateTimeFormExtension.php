<?php

namespace Oro\Bundle\SoapBundle\Form\Extension;

use Oro\Bundle\FormBundle\Form\Type\OroDateTimeType;

class DateTimeFormExtension extends DateFormExtension
{
    /**
     * {@inheritdoc}
     */
    public function getExtendedType()
    {
        return OroDateTimeType::NAME;
    }
}
