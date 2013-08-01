<?php

namespace Pim\Bundle\FilterBundle\Tests\Unit\Form\Type\Filter;

use Oro\Bundle\FilterBundle\Form\Type\Filter\NumberFilterType;
use Oro\Bundle\FilterBundle\Form\Type\Filter\FilterType;
use Pim\Bundle\FilterBundle\Form\Type\Filter\CurrencyFilterType;
use Oro\Bundle\FilterBundle\Tests\Unit\Form\Type\Filter\NumberFilterTypeTest;

/**
 * Test related class
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class CurrencyFilterTypeTest extends NumberFilterTypeTest
{
    /**
     * @var ScopeFilterType
     */
    protected $type;

    /**
     * @staticvar array
     */
    protected static $currencyChoices = array(
        'EUR' => 'EUR',
        'USD' => 'USD'
    );

    /**
     * @staticvar array
     */
    protected static $operatorChoices = array(
        NumberFilterType::TYPE_EQUAL => 'label_type_equal',
        NumberFilterType::TYPE_GREATER_EQUAL => 'label_type_greater_equal',
        NumberFilterType::TYPE_GREATER_THAN => 'label_type_greater_than',
        NumberFilterType::TYPE_LESS_EQUAL => 'label_type_less_equal',
        NumberFilterType::TYPE_LESS_THAN => 'label_type_less_than',
    );

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->markTestSkipped(
            'Due to Symfony 2.3 Upgrade, cf https://github.com/symfony/symfony/blob/master/UPGRADE-2.1.md'
        );
        parent::setUp();

        $translator = $this->createMockTranslator();
        $currencyManager = $this->createMockCurrencyManager();

        $this->type = new CurrencyFilterType($translator, $currencyManager);
        $this->factory->addType(new FilterType($translator));
        $this->factory->addType(new NumberFilterType($translator));
    }

    /**
     * Create mock currency manager
     *
     * @return Pim\Bundle\ConfigBundle\Manager\CurrencyManager
     */
    protected function createMockCurrencyManager()
    {
        $currencyManager = $this->getMockBuilder('Pim\Bundle\ConfigBundle\Manager\CurrencyManager')
                                ->disableOriginalConstructor()
                                ->getMock();

        $currencyManager->expects($this->any())
                        ->method('getActiveCodes')
                        ->will($this->returnValue(self::$currencyChoices));

        return $currencyManager;
    }

    /**
     * {@inheritdoc}
     */
    protected function getTestFormType()
    {
        return $this->type;
    }

    /**
     * Test related method
     */
    public function testGetName()
    {
        $this->assertEquals(CurrencyFilterType::NAME, $this->type->getName());
        $this->assertEquals(NumberFilterType::NAME, $this->type->getParent());
    }

    /**
     * {@inheritDoc}
     */
    public function setDefaultOptionsDataProvider()
    {
        return array(
            array(
                'defaultOptions' => array(
                    'field_type'        => 'number',
                    'operator_choices'  => self::$operatorChoices,
                    'operator_type'     => 'choice',
                    'operator_options'  => array(),
                    'currency_choices'  => self::$currencyChoices,
                    'currency_type'     => 'choice',
                    'currency_options'  => array(),
                    'data_type'         => CurrencyFilterType::DATA_DECIMAL,
                    'formatter_options' => array()
                )
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function bindDataProvider()
    {
        return array(
            'not formatted number' => array(
                'bindData' =>
                    array('type' => CurrencyFilterType::TYPE_EQUAL, 'value' => '12345.67890', 'currency' => 'EUR'),
                'formData' =>
                    array('type' => CurrencyFilterType::TYPE_EQUAL, 'value' => 12345.6789, 'currency' => 'EUR'),
                'viewData' => array(
                    'value' =>
                        array('type' => CurrencyFilterType::TYPE_EQUAL, 'value' => '12,345.68', 'currency' => 'EUR')
                ),
                'customOptions' => array(
                    'field_options' => array('grouping' => true, 'precision' => 2)
                ),
            ),
            'formatted number' => array(
                'bindData' =>
                    array('type' => CurrencyFilterType::TYPE_EQUAL, 'value' => '12,345.68', 'currency' => 'USD'),
                'formData' =>
                    array('type' => CurrencyFilterType::TYPE_EQUAL, 'value' => 12345.68, 'currency' => 'USD'),
                'viewData' => array(
                    'value' =>
                        array('type' => CurrencyFilterType::TYPE_EQUAL, 'value' => '12,345.68', 'currency' => 'USD')
                ),
                'customOptions' => array(
                    'field_options' => array('grouping' => true, 'precision' => 2)
                )
            ),
            'integer' => array(
                'bindData' =>
                    array('type' => CurrencyFilterType::TYPE_LESS_THAN, 'value' => '12345.67890', 'currency' => 'USD'),
                'formData' =>
                    array('type' => CurrencyFilterType::TYPE_LESS_THAN, 'value' => 12345, 'currency' => 'USD'),
                'viewData' => array(
                    'value' =>
                        array('type' => CurrencyFilterType::TYPE_LESS_THAN, 'value' => '12345', 'currency' => 'USD'),
                    'formatter_options' => array(
                        'decimals' => 0,
                        'grouping' => false,
                        'orderSeparator' => '',
                        'decimalSeparator' => '.'
                    )
                ),
                'customOptions' => array(
                    'field_type' => 'integer',
                    'data_type' => CurrencyFilterType::DATA_INTEGER
                )
            ),
            'invalid format' => array(
                'bindData' =>
                    array('type' => CurrencyFilterType::TYPE_EQUAL, 'value' => 'abcd.67890', 'currency' => 'EUR'),
                'formData' => array('type' => CurrencyFilterType::TYPE_EQUAL, 'currency' => 'EUR'),
                'viewData' => array(
                    'value' =>
                        array('type' => CurrencyFilterType::TYPE_EQUAL, 'value' => 'abcd.67890', 'currency' => 'EUR')
                ),
                'customOptions' => array(
                    'field_type' => 'money'
                )
            )
        );
    }
}
