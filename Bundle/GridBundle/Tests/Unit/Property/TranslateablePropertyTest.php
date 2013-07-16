<?php

namespace Oro\Bundle\GridBundle\Tests\Unit\Property;

use Oro\Bundle\GridBundle\Datagrid\ResultRecord;
use Oro\Bundle\GridBundle\Datagrid\ResultRecordInterface;
use Oro\Bundle\GridBundle\Property\TranslateableProperty;

class TranslateablePropertyTest extends \PHPUnit_Framework_TestCase
{
    const FIELD_NAME = 'testFieldName';
    const FIELD_ALIAS = 'testFieldName';
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
        $this->property = new TranslateableProperty(self::FIELD_NAME, $this->translator, null, 'domain', 'locale');
    }

    public function testGetValueByName()
    {
        $record = $this->createRecord(array(self::FIELD_NAME => 'testData'));

        $this->translator->expects($this->once())
            ->method('trans')
            ->with('testData', array(), 'domain', 'locale')
            ->will($this->returnValue('translatedValue'));

        $this->assertEquals('translatedValue', $this->property->getValue($record));
    }

    public function testGetValueByAlias()
    {
        $property = new TranslateableProperty(self::FIELD_NAME, $this->translator, self::FIELD_ALIAS);
        $record = $this->createRecord(array(self::FIELD_NAME => 'testData', self::FIELD_ALIAS => 'aliasData'));

        $this->translator->expects($this->once())
            ->method('trans')
            ->with('aliasData', array(), null, null)
            ->will($this->returnValue('aliasTranslatedValue'));

        $this->assertEquals('aliasTranslatedValue', $property->getValue($record));
    }

    /**
     * @param array $data
     * @return ResultRecordInterface
     */
    private function createRecord(array $data)
    {
        return new ResultRecord($data);
    }

    public function tearDown()
    {
        unset($this->translator);
        unset($this->property);
    }
}
