<?php

namespace Pim\Bundle\GridBundle\Tests\Unit\Filter\ORM\Flexible;

use Oro\Bundle\FilterBundle\Form\Type\Filter\TextFilterType;
use Oro\Bundle\GridBundle\Filter\ORM\StringFilter;
use Pim\Bundle\GridBundle\Filter\ORM\Flexible\FlexibleStringFilter;

/**
 * Test related class
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FlexibleStringFilterTest extends FlexibleFilterTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function createTestFilter($flexibleRegistry)
    {
        $parentFilter = new StringFilter($this->getTranslatorMock());

        return new FlexibleStringFilter($flexibleRegistry, $parentFilter);
    }

    public function filterDataProvider()
    {
        return array(
            'correct_equals' => array(
                'data' => array('value' => 'test', 'type' => TextFilterType::TYPE_EQUAL),
                'expectRepositoryCalls' => array(
                    array('applyFilterByAttribute', array(self::TEST_FIELD, 'test', '='), null)
                )
            ),
            'correct_like' => array(
                'data' => array('value' => 'test', 'type' => TextFilterType::TYPE_CONTAINS),
                'expectRepositoryCalls' => array(
                    array('applyFilterByAttribute', array(self::TEST_FIELD, '%test%', 'LIKE'), null)
                ),
                'options' => array(
                    'format' => '%%%s%%'
                )
            ),
            'incorrect' => array(
                'data' => array(),
                'expectRepositoryCalls' => array()
            )
        );
    }
}
