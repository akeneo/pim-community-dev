<?php

namespace Pim\Bundle\GridBundle\Tests\Unit\Filter\ORM\Flexible;

use Oro\Bundle\FilterBundle\Form\Type\Filter\DateRangeFilterType;
use Oro\Bundle\GridBundle\Filter\ORM\DateRangeFilter;

/**
 * Test related class
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AbstractFlexibleDateFilterTest extends FlexibleFilterTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function createTestFilter($flexibleRegistry)
    {
        $parentFilter = new DateRangeFilter($this->getTranslatorMock());

        return $this->getMockBuilder('Pim\Bundle\GridBundle\Filter\ORM\Flexible\AbstractFlexibleDateFilter')
            ->setConstructorArgs(array($flexibleRegistry, $parentFilter))
            ->getMockForAbstractClass();
    }

    public function testConstructWithIncorrectFilter()
    {
        $this->setExpectedException(
            '\InvalidArgumentException',
            'Parent filter must be an instance of Oro\Bundle\GridBundle\Filter\ORM\AbstractDateFilter'
        );
        $flexibleRegistry = $this->getMock('\Pim\Bundle\FlexibleEntityBundle\Manager\FlexibleManagerRegistry');
        $incorrectParentFilter = $this->getMockForAbstractClass(
            '\Oro\Bundle\GridBundle\Filter\FilterInterface',
            array(),
            '',
            false
        );

        $this->getMockBuilder('Pim\Bundle\GridBundle\Filter\ORM\Flexible\AbstractFlexibleDateFilter')
            ->setConstructorArgs(array($flexibleRegistry, $incorrectParentFilter))
            ->getMockForAbstractClass();
    }

    public function filterDataProvider()
    {
        date_default_timezone_set('UTC');

        return array(
            'no_data' => array(
                'data' => array(),
                'expectRepositoryCalls' => array()
            ),
            'between' => array(
                'data' => array(
                    'value' => array(
                        'start' => new \DateTime('2013-01-01'),
                        'end' => new \DateTime('2014-01-01'),
                    ),
                    'type' => DateRangeFilterType::TYPE_BETWEEN
                ),
                'expectRepositoryCalls' => array(
                    array('applyFilterByAttribute', array(self::TEST_FIELD, '2013-01-01', '>='), null),
                    array('applyFilterByAttribute', array(self::TEST_FIELD, '2014-01-01', '<='), null),
                )
            ),
            'between_only_start' => array(
                'data' => array(
                    'value' => array(
                        'start' => new \DateTime('2013-01-01'),
                    ),
                    'type' => DateRangeFilterType::TYPE_BETWEEN
                ),
                'expectRepositoryCalls' => array(
                    array('applyFilterByAttribute', array(self::TEST_FIELD, '2013-01-01', '>='), null),
                )
            ),
            'between_only_end' => array(
                'data' => array(
                    'value' => array(
                        'end' => new \DateTime('2014-01-01'),
                    ),
                    'type' => DateRangeFilterType::TYPE_BETWEEN
                ),
                'expectRepositoryCalls' => array(
                    array('applyFilterByAttribute', array(self::TEST_FIELD, '2014-01-01', '<='), null),
                )
            ),
            'not_between' => array(
                'data' => array(
                    'value' => array(
                        'start' => new \DateTime('2013-01-01'),
                        'end' => new \DateTime('2014-01-01'),
                    ),
                    'type' => DateRangeFilterType::TYPE_NOT_BETWEEN
                ),
                'expectRepositoryCalls' => array(
                    array(
                        'applyFilterByAttribute',
                        array(
                            self::TEST_FIELD,
                            array('from' => '2013-01-01', 'to' => '2014-01-01'),
                            array('from' => '<', 'to' => '>')
                        ),
                        null
                    ),
                )
            ),
        );
    }
}
