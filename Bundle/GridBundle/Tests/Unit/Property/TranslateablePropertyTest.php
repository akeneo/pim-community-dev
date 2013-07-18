<?php

namespace Oro\Bundle\GridBundle\Tests\Unit\Property;

use Oro\Bundle\GridBundle\Datagrid\ResultRecord;
use Oro\Bundle\GridBundle\Property\TranslateableProperty;

class TranslateablePropertyTest extends \PHPUnit_Framework_TestCase
{
    const FIELD_NAME         = 'testFieldName';
    const FIELD_ALIAS        = 'testFieldName';
    const TRANSLATION_DOMAIN = 'testDomain';
    const LOCALE             = 'testLocale';

    const TRANSLATION_PREFIX = 'trans_';

    /**
     * @return array
     */
    public function getValueDataProvider()
    {
        return array(
            'get value by name' => array(
                'expectedValue' => self::TRANSLATION_PREFIX . 'fieldNameValue',
                'data'          => array(self::FIELD_NAME => 'fieldNameValue'),
                'name'          => self::FIELD_NAME,
            ),
            'get value by alias' => array(
                'expectedValue' => self::TRANSLATION_PREFIX . 'fieldAliasValue',
                'data'          => array(self::FIELD_NAME => 'fieldNameValue', self::FIELD_ALIAS => 'fieldAliasValue'),
                'name'          => self::FIELD_NAME,
                'alias'         => self::FIELD_ALIAS,
                'domain'        => self::TRANSLATION_DOMAIN,
                'locale'        => self::LOCALE,
            )
        );
    }

    /**
     * @param string $expectedValue
     * @param array $data
     * @param string $name
     * @param string|null $alias
     * @param string|null $domain
     * @param string|null $locale
     *
     * @dataProvider getValueDataProvider
     */
    public function testGetValue($expectedValue, array $data, $name, $alias = null, $domain = null, $locale = null)
    {
        // prepare mock
        $translator = $this->getMockBuilder('Symfony\Component\Translation\TranslatorInterface')
            ->disableOriginalConstructor()
            ->setMethods(array('trans'))
            ->getMockForAbstractClass();

        $prefix = self::TRANSLATION_PREFIX;
        $translator->expects($this->once())
            ->method('trans')
            ->with($this->isType('string'), array(), $domain, $locale)
            ->will(
                $this->returnCallback(
                    function ($id) use ($prefix) {
                        return $prefix . $id;
                    }
                )
            );

        // test
        $property = new TranslateableProperty($name, $translator, $alias, $domain, $locale);
        $actualValue = $property->getValue(new ResultRecord($data));

        $this->assertEquals($expectedValue, $actualValue);
    }
}
