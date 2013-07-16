<?php

namespace Oro\Bundle\GridBundle\Tests\Unit\Property;

use Oro\Bundle\GridBundle\Datagrid\ResultRecord;
use Oro\Bundle\GridBundle\Datagrid\ResultRecordInterface;
use Oro\Bundle\GridBundle\Property\TranslateableProperty;

class TranslateablePropertyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var TranslateableProperty
     */
    protected $property;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $translator;

    public function setUp()
    {
        $this->translator = $this->getMockForAbstractClass('Symfony\Component\Translation\TranslatorInterface');
        $this->property = new TranslateableProperty('testFieldName', $this->translator);
    }

    public function testGetValueByName()
    {
        $record = $this->createRecord(array('testFieldName' => 'testData'));

        $this->translator->expects($this->once())
            ->method('trans')
            ->with('testData')
            ->will($this->returnValue('translatedValue'));

        $this->assertEquals('translatedValue', $this->property->getValue($record));
    }

    public function testGetValueByAlias()
    {
        $property = new TranslateableProperty('testFieldName', $this->translator, 'aliasFieldName');
        $record = $this->createRecord(array('testFieldName' => 'testData', 'aliasFieldName' => 'aliasData'));

        $this->translator->expects($this->once())
            ->method('trans')
            ->with('aliasData')
            ->will($this->returnValue('aliasTranslatedValue'));

        $this->assertEquals('aliasTranslatedValue', $property->getValue($record));
    }

    /**
     * @param mixed $data
     * @return ResultRecordInterface
     */
    private function createRecord($data)
    {
        return new ResultRecord($data);
    }
}
