<?php

namespace Pim\Bundle\GridBundle\Tests\Unit\Filter\ORM\Flexible;

use Oro\Bundle\FilterBundle\Form\Type\Filter\NumberFilterType;
use Oro\Bundle\GridBundle\Filter\ORM\NumberFilter;
use Pim\Bundle\GridBundle\Filter\ORM\Flexible\FlexibleNumberFilter;

/**
 * Test related class
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
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
