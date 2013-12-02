<?php

namespace Oro\Bundle\ImportExportBundle\Tests\Unit\Job;

use Oro\Bundle\ImportExportBundle\Job\JobResult;
use Symfony\Component\PropertyAccess\PropertyAccess;

class JobResultTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider propertiesDataProvider
     * @param string $property
     * @param mixed $value
     */
    public function testSettersAndGetters($property, $value)
    {
        $obj = new JobResult();

        $accessor = PropertyAccess::createPropertyAccessor();
        $accessor->setValue($obj, $property, $value);
        $this->assertEquals($value, $accessor->getValue($obj, $property));
    }

    public function propertiesDataProvider()
    {
        return array(
            array('context', $this->getMockForAbstractClass('Oro\Bundle\ImportExportBundle\Context\ContextInterface')),
            array('jobId', 'test'),
            array('jobCode', 'test'),
            array('successful', true)
        );
    }

    public function testFailureExceptions()
    {
        $obj = new JobResult();
        $obj->addFailureException('Error 1');
        $obj->addFailureException('Error 2');
        $this->assertEquals(array('Error 1', 'Error 2'), $obj->getFailureExceptions());
    }
}
