<?php

namespace Oro\Bundle\GridBundle\Tests\Unit\Filter\ORM;

use Oro\Bundle\FilterBundle\Form\Type\Filter\BooleanFilterType;
use Oro\Bundle\GridBundle\Filter\ORM\BooleanFilter;

class BooleanFilterTest extends FilterTestCase
{
    /**
     * @return BooleanFilter
     */
    protected function createTestFilter()
    {
        return new BooleanFilter($this->getTranslatorMock());
    }

    /**
     * @return array
     */
    public function filterDataProvider()
    {
        $fieldExpression   = self::TEST_ALIAS . '.' . self::TEST_FIELD;
        $expressionFactory = $this->getExpressionFactory();
        $compareExpression = $expressionFactory->neq($fieldExpression, 'false');

        $summaryExpression = $expressionFactory->andX(
            $expressionFactory->isNotNull($fieldExpression),
            $compareExpression
        );

        return array(
            'not_array_value' => array(
                'data' => '',
                'expectProxyQueryCalls' => array()
            ),
            'no_data' => array(
                'data' => array(),
                'expectProxyQueryCalls' => array()
            ),
            'no_value' => array(
                'data' => array('value' => ''),
                'expectProxyQueryCalls' => array()
            ),
            'incorrect_value' => array(
                'data' => array('value' => 'incorrect_value'),
                'expectProxyQueryCalls' => array()
            ),
            'value_yes_nullable' => array(
                'data' => array('value' => BooleanFilterType::TYPE_YES),
                'expectProxyQueryCalls' => array(
                    array(
                        'andWhere',
                        array($summaryExpression),
                        null
                    )
                )
            ),
            'value_yes_not_nullable' => array(
                'data' => array('value' => BooleanFilterType::TYPE_YES),
                'expectProxyQueryCalls' => array(
                    array(
                        'andWhere',
                        array($compareExpression),
                        null
                    )
                ),
                'options' => array('nullable' => false)
            ),
            'value_no_nullable' => array(
                'data' => array('value' => BooleanFilterType::TYPE_NO),
                'expectProxyQueryCalls' => array(
                    array(
                        'andWhere',
                        array($expressionFactory->not($summaryExpression)),
                        null
                    )
                )
            ),
            'value_no_not_nullable' => array(
                'data' => array('value' => BooleanFilterType::TYPE_NO),
                'expectProxyQueryCalls' => array(
                    array(
                        'andWhere',
                        array($expressionFactory->not($compareExpression)),
                        null
                    )
                ),
                'options' => array('nullable' => false)
            ),
        );
    }

    public function testGetDefaultOptions()
    {
        $this->assertEquals(
            array(
                'form_type' => BooleanFilterType::NAME
            ),
            $this->model->getDefaultOptions()
        );
    }
}
