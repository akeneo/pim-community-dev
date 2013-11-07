<?php

namespace Oro\Bundle\FormBundle\Tests\Unit\Form\Type;

use Symfony\Component\Form\Test\FormIntegrationTestCase;

use Oro\Bundle\FormBundle\Form\Type\OroMoneyType;

class OroMoneyTypeTest extends FormIntegrationTestCase
{
    /**
     * @var OroMoneyType
     */
    protected $formType;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $localeSettings;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $numberFormatter;

    protected function setUp()
    {
        parent::setUp();

        $this->localeSettings = $this->getMockBuilder('Oro\Bundle\LocaleBundle\Model\LocaleSettings')
            ->disableOriginalConstructor()
            ->setMethods(array('getLocale', 'getCurrency', 'getCurrencySymbolByCurrency'))
            ->getMock();

        $this->numberFormatter = $this->getMockBuilder('Oro\Bundle\LocaleBundle\Formatter\NumberFormatter')
            ->disableOriginalConstructor()
            ->setMethods(array('isCurrencySymbolPrepend', 'getAttribute'))
            ->getMock();

        $this->formType = new OroMoneyType($this->localeSettings, $this->numberFormatter);
    }

    protected function tearDown()
    {
        parent::tearDown();

        unset($this->formType);
        unset($this->localeSettings);
    }

    public function testGetName()
    {
        $this->assertEquals(OroMoneyType::NAME, $this->formType->getName());
    }

    public function testGetParent()
    {
        $this->assertEquals('money', $this->formType->getParent());
    }

    /**
     * @return array
     */
    public function bindDataProvider()
    {
        return array(
            'default en locale' => array(
                'locale'         => 'en',
                'currency'       => 'USD',
                'currencySymbol' => '$',
                'symbolPrepend'  => true,
                'data'           => 11.22,
                'viewData'       => array(
                    'money_pattern' => '{{ currency }}{{ widget }}',
                    'currency_symbol' => '$',
                    'currency_symbol_prepend' => true
                ),
            ),
            'default ru locale' => array(
                'locale'         => 'ru',
                'currency'       => 'RUR',
                'currencySymbol' => 'руб.',
                'symbolPrepend'  => false,
                'data'           => 11.22,
                'viewData'       => array(
                    'money_pattern' => '{{ widget }}{{ currency }}',
                    'currency_symbol' => 'руб.',
                    'currency_symbol_prepend' => false
                ),
            ),
            'custom currency' => array(
                'locale'         => 'en',
                'currency'       => 'EUR',
                'currencySymbol' => '€',
                'symbolPrepend'  => true,
                'data'           => 11.22,
                'viewData'       => array(
                    'money_pattern' => '{{ currency }}{{ widget }}',
                    'currency_symbol' => '€',
                    'currency_symbol_prepend' => true
                ),
            ),
        );
    }

    /**
     * @param string $locale
     * @param string $currency
     * @param string $currencySymbol
     * @param bool $symbolPrepend
     * @param float $data
     * @param array $viewData
     * @param array $options
     * @dataProvider bindDataProvider
     */
    public function testBindData(
        $locale,
        $currency,
        $currencySymbol,
        $symbolPrepend,
        $data,
        array $viewData,
        array $options = array()
    ) {
        $this->localeSettings->expects($this->any())
            ->method('getLocale')
            ->will($this->returnValue($locale));
        $this->localeSettings->expects($this->any())
            ->method('getCurrency')
            ->will($this->returnValue($currency));
        $this->localeSettings->expects($this->any())
            ->method('getCurrencySymbolByCurrency')
            ->with($currency)
            ->will($this->returnValue($currencySymbol));

        $this->numberFormatter->expects($this->any())
            ->method('isCurrencySymbolPrepend')
            ->with($currency)
            ->will($this->returnValue($symbolPrepend));

        $this->numberFormatter->expects($this->any())
            ->method('getAttribute')
            ->with(\NumberFormatter::GROUPING_USED)
            ->will($this->returnValue(1));

        $form = $this->factory->create($this->formType, null, $options);

        $form->submit($data);

        $this->assertTrue($form->isSynchronized());
        $this->assertEquals($data, $form->getData());

        $view = $form->createView();

        foreach ($viewData as $key => $value) {
            $this->assertArrayHasKey($key, $view->vars);
            $this->assertEquals($value, $view->vars[$key]);
        }
    }
}
