<?php

namespace Oro\Bundle\ImportExportBundle\Tests\Unit\Form\Model;

use Oro\Bundle\ImportExportBundle\Form\Model\ImportData;
use Symfony\Component\PropertyAccess\PropertyAccess;

class ImportDataTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider propertiesDataProvider
     * @param string $property
     * @param mixed $value
     */
    public function testSettersAndGetters($property, $value)
    {
        $obj = new ImportData();

        $accessor = PropertyAccess::createPropertyAccessor();
        $accessor->setValue($obj, $property, $value);
        $this->assertEquals($value, $accessor->getValue($obj, $property));
    }

    public function propertiesDataProvider()
    {
        return array(
            array('file', 'test'),
            array('processorAlias', 'test')
        );
    }
}
