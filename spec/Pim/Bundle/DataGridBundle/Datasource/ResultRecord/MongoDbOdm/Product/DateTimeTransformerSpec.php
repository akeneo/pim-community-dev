<?php

namespace spec\Pim\Bundle\DataGridBundle\Datasource\ResultRecord\MongoDbOdm\Product;

use PhpSpec\ObjectBehavior;

/**
 * @require \MongoDate
 */
class DateTimeTransformerSpec extends ObjectBehavior
{
    function it_transforms_a_mongodate_to_a_datetime(\MongoDate $date)
    {
        $this->transform($date)->shouldReturnAnInstanceOf('DateTime');
    }
}
