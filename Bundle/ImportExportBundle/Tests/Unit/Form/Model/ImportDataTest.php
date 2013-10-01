<?php

namespace Oro\Bundle\ImportExportBundle\Tests\Unit\Form\Model;

use Oro\Bundle\ImportExportBundle\Form\Model\ImportData;

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

        call_user_func_array(array($obj, 'set' . ucfirst($property)), array($value));
        $this->assertEquals($value, call_user_func_array(array($obj, 'get' . ucfirst($property)), array()));
    }

    public function propertiesDataProvider()
    {
        return array(
            array('file', 'test'),
            array('processorAlias', 'test')
        );
    }
}
