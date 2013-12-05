<?php

namespace Pim\Bundle\GridBundle\Tests\Unit\Filter\ORM;

use Oro\Bundle\FilterBundle\Form\Type\Filter\BooleanFilterType;
use Oro\Bundle\GridBundle\Tests\Unit\Filter\ORM\FilterTestCase;

use Pim\Bundle\GridBundle\Filter\ORM\CompletenessFilter;

/**
 *
 * Enter description here ...
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class CompletenessFilterTest extends FilterTestCase
{
    /**
     * @var CompletenessFilter
     */
    protected $model;

    /**
     * {@inheritdoc}
     */
    protected function createTestFilter()
    {
        return new CompletenessFilter($this->getTranslatorMock());
    }

    /**
     * Test related method
     */
    public function testGetDefaultOptions()
    {
        $this->assertEquals(array('form_type' => BooleanFilterType::NAME), $this->model->getDefaultOptions());
    }

    /**
     * {@inheritdoc}
     */
    public function filterDataProvider()
    {
        return array(
            'not_array_value' => array(
                'data' => '',
                'expectProxyQuerycalls' => array()
            ),
            'no_data' => array(
                'data' => array(),
                'expectProxyQuerycalls' => array()
            ),
            'no_value' => array(
                'data' => array('value' => ''),
                'expectProxyQuerycalls' => array()
            ),
            'wrong_value' => array(
                'data' => array('value' => 'test'),
                'expectProxyQuerycalls' => array()
            )
        );
    }
}
