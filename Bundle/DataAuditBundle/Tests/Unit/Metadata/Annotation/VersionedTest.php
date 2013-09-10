<?php

namespace Oro\Bundle\DataAuditBundle\Tests\Unit\Metadata\Annotation;

use Oro\Bundle\DataAuditBundle\Metadata\Annotation\Versioned;

class VersionedTest extends \PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        $versioned =  new Versioned(array('value' => '__toString'));
        $versioned2 =  new Versioned(array('method' => '__toString'));

        $this->assertAttributeEquals('__toString', 'method', $versioned);
        $this->assertAttributeEquals('__toString', 'method', $versioned2);
    }
}
