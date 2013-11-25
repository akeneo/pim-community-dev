<?php

namespace Pim\Bundle\GridBundle\Tests\Unit\Filter\ORM\Flexible;

use Oro\Bundle\FilterBundle\Form\Type\Filter\NumberFilterType;
use Oro\Bundle\GridBundle\Filter\ORM\NumberFilter;
use Pim\Bundle\GridBundle\Filter\ORM\Flexible\FlexibleNumberFilter;

class FlexibleNumberFilterTest extends FlexibleFilterTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function createTestFilter($flexibleRegistry)
    {
        $parentFilter = new NumberFilter($this->getTranslatorMock());
        return new FlexibleNumberFilter($flexibleRegistry, $parentFilter);
    }

    public function filterDataProvider()
    {
        return array(
            'correct_equals' => array(
                'data' => array('value' => 123, 'type' => NumberFilterType::TYPE_EQUAL),
                'expectRepositoryCalls' => array(
                    array('applyFilterByAttribute', array(self::TEST_FIELD, 123, '='), null)
                )
            ),
            'incorrect' => array(
                'data' => array(),
                'expectRepositoryCalls' => array()
            )
        );
    }
}
