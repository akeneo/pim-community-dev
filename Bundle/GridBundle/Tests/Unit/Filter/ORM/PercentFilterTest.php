<?php

namespace Oro\Bundle\GridBundle\Tests\Unit\Filter\ORM;

use Oro\Bundle\FilterBundle\Form\Type\Filter\NumberFilterType;
use Oro\Bundle\GridBundle\Filter\ORM\PercentFilter;

class PercentFilterTest extends FilterTestCase
{
    /**
     * {@inheritDoc}
     */
    protected function createTestFilter()
    {
        return new PercentFilter($this->getTranslatorMock());
    }

    /**
     * {@inheritDoc}
     */
    public function filterDataProvider()
    {
        return array(
            'no_data' => array(
                'data' => array(),
                'expectProxyQueryCalls' => array()
            ),
            'percent_data' => array(
                'data' => array('value' => 12, 'type' => NumberFilterType::TYPE_EQUAL),
                'expectProxyQueryCalls' => array(
                    array('getUniqueParameterId', array(), 'p1'),
                    array('andWhere',
                        array(
                            $this->getExpressionFactory()->eq(
                                self::TEST_ALIAS . '.' . self::TEST_FIELD,
                                ':' . self::TEST_NAME . '_p1'
                            )
                        ), null),
                    array('setParameter', array(self::TEST_NAME . '_p1', 0.12), null)
                )
            )
        );
    }
}
