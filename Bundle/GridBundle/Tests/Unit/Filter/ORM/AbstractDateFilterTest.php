<?php

namespace Oro\Bundle\GridBundle\Tests\Unit\Filter\ORM;

use Oro\Bundle\GridBundle\Filter\ORM\AbstractDateFilter;

use Oro\Bundle\FilterBundle\Form\Type\Filter\DateRangeFilterType;

class AbstractDateFilterTest extends FilterTestCase
{
    /**
     * @var AbstractDateFilter
     */
    protected $model;

    /**
     * @return AbstractDateFilter|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function createTestFilter()
    {
        return $this->getMockBuilder('Oro\Bundle\GridBundle\Filter\ORM\AbstractDateFilter')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
    }

    /**
     * @return array
     */
    public function parseDataDataProvider()
    {
        return array(
            'no_data'   => array(array(), false),
            'not_array' => array('some_string', false),
            'no_value' => array(array('key' => 'some_string'), false),
            'no_dates' => array(array('value' => array()), false),
            'incorrect_start_date' => array(
                array('value' => array('start' => 'incorrect_date', 'end' => new \DateTime())),
                false
            ),
            'incorrect_end_date' => array(
                array('value' => array('end' => 'incorrect_date', 'start' => new \DateTime())),
                false
            ),
            'correct_dates' => array(
                array(
                    'value' => array(
                        'start' => new \DateTime('2013-04-08', new \DateTimeZone('UTC')),
                        'end' => new \DateTime('2013-05-08', new \DateTimeZone('UTC'))),
                    'type' => DateRangeFilterType::TYPE_BETWEEN
                ),
                array(
                    'date_start' => $this->dateTimeToString(new \DateTime('2013-04-08', new \DateTimeZone('UTC'))),
                    'date_end' => $this->dateTimeToString(new \DateTime('2013-05-08', new \DateTimeZone('UTC'))),
                    'type' => DateRangeFilterType::TYPE_BETWEEN
                )
            ),
            'correct_dates_between_as_default_type' => array(
                array(
                    'value' => array(
                        'start' => new \DateTime('2013-04-08', new \DateTimeZone('UTC')),
                        'end' => new \DateTime('2013-05-08', new \DateTimeZone('UTC'))),
                    'type'  => 'SomeNotValidValue'
                ),
                array(
                    'date_start' => $this->dateTimeToString(new \DateTime('2013-04-08', new \DateTimeZone('UTC'))),
                    'date_end'   => $this->dateTimeToString(new \DateTime('2013-05-08', new \DateTimeZone('UTC'))),
                    'type'       => DateRangeFilterType::TYPE_BETWEEN
                )
            ),
            'only_start_date' => array(
                array(
                    'value' => array('start' => new \DateTime('2013-04-08', new \DateTimeZone('UTC'))),
                    'type' => DateRangeFilterType::TYPE_BETWEEN
                ),
                array(
                    'date_start' => $this->dateTimeToString(new \DateTime('2013-04-08', new \DateTimeZone('UTC'))),
                    'date_end' => null,
                    'type' => DateRangeFilterType::TYPE_BETWEEN
                )
            ),
            'only_end_date' => array(
                array(
                    'value' => array('end' => new \DateTime('2013-04-08', new \DateTimeZone('UTC'))),
                    'type' => DateRangeFilterType::TYPE_BETWEEN
                ),
                array(
                    'date_start' => null,
                    'date_end' => $this->dateTimeToString(new \DateTime('2013-04-08', new \DateTimeZone('UTC'))),
                    'type' => DateRangeFilterType::TYPE_BETWEEN
                )
            ),
            'more_than_date' => array(
                array(
                    'value' => array('start' => new \DateTime('2013-04-08', new \DateTimeZone('UTC'))),
                    'type'  => DateRangeFilterType::TYPE_MORE_THAN
                ),
                array(
                    'date_start' => $this->dateTimeToString(new \DateTime('2013-04-08', new \DateTimeZone('UTC'))),
                    'date_end'   => null,
                    'type'       => DateRangeFilterType::TYPE_MORE_THAN
                )
            ),
            'less_than_date' => array(
                array(
                    'value' => array('end' => new \DateTime('2013-04-08', new \DateTimeZone('UTC'))),
                    'type'  => DateRangeFilterType::TYPE_LESS_THAN
                ),
                array(
                    'date_start' => null,
                    'date_end'   => $this->dateTimeToString(new \DateTime('2013-04-08', new \DateTimeZone('UTC'))),
                    'type'       => DateRangeFilterType::TYPE_LESS_THAN
                )
            ),
        );
    }

    /**
     * @param mixed $data
     * @param mixed $expected
     *
     * @dataProvider parseDataDataProvider
     */
    public function testParseData($data, $expected)
    {
        $this->assertEquals($expected, $this->model->parseData($data));
    }

    /**
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function filterDataProvider()
    {
        return array(
            'no_data' => array(
                'data' => array(),
                'expectProxyQueryCalls' => array()
            ),
            'between' => array(
                'data' => array(
                    'value' => array(
                        'start' => new \DateTime('2012-01-01', new \DateTimeZone('UTC')),
                        'end' => new \DateTime('2013-01-01', new \DateTimeZone('UTC'))
                    ),
                    'type'  => DateRangeFilterType::TYPE_BETWEEN
                ),
                'expectProxyQueryCalls' => array(
                    array('getUniqueParameterId', array(), 'p1'),
                    array('getUniqueParameterId', array(), 'p2'),
                    array('andWhere',
                        array(
                            $this->getExpressionFactory()->gte(
                                self::TEST_ALIAS . '.' . self::TEST_FIELD,
                                ':' . self::TEST_NAME . '_p1'
                            )
                        ), null),
                    array('andWhere',
                        array(
                            $this->getExpressionFactory()->lte(
                                self::TEST_ALIAS . '.' . self::TEST_FIELD,
                                ':' . self::TEST_NAME . '_p2'
                            )
                        ), null),
                    array('setParameter',
                        array(
                            self::TEST_NAME . '_p1',
                            $this->dateTimeToString(new \DateTime('2012-01-01', new \DateTimeZone('UTC')))
                        ),
                        null
                    ),
                    array('setParameter',
                        array(
                            self::TEST_NAME . '_p2',
                            $this->dateTimeToString(new \DateTime('2013-01-01', new \DateTimeZone('UTC')))
                        ),
                        null
                    ),
                )
            ),
            'not_between' => array(
                'data' => array(
                    'value' => array(
                        'start' => new \DateTime('2012-01-01', new \DateTimeZone('UTC')),
                        'end' => new \DateTime('2013-01-01', new \DateTimeZone('UTC'))
                    ),
                    'type'  => DateRangeFilterType::TYPE_NOT_BETWEEN
                ),
                'expectProxyQueryCalls' => array(
                    array('getUniqueParameterId', array(), 'p1'),
                    array('getUniqueParameterId', array(), 'p2'),
                    array('andWhere',
                        array(
                            $this->getExpressionFactory()->orX(
                                $this->getExpressionFactory()->lt(
                                    self::TEST_ALIAS . '.' . self::TEST_FIELD,
                                    ':' . self::TEST_NAME . '_p1'
                                ),
                                $this->getExpressionFactory()->gt(
                                    self::TEST_ALIAS . '.' . self::TEST_FIELD,
                                    ':' . self::TEST_NAME . '_p2'
                                )
                            )
                        ), null),
                    array('setParameter',
                        array(
                            self::TEST_NAME . '_p1',
                            $this->dateTimeToString(new \DateTime('2012-01-01', new \DateTimeZone('UTC')))),
                        null
                    ),
                    array('setParameter',
                        array(
                            self::TEST_NAME . '_p2',
                            $this->dateTimeToString(new \DateTime('2013-01-01', new \DateTimeZone('UTC')))
                        ),
                        null
                    ),
                )
            ),
            'date_more_than' => array(
                'data'                  => array(
                    'value' => array('start' => new \DateTime('2012-01-01', new \DateTimeZone('UTC'))),
                    'type'  => DateRangeFilterType::TYPE_MORE_THAN
                ),
                'expectProxyQueryCalls' => array(
                    array('getUniqueParameterId', array(), 'p1'),
                    array('getUniqueParameterId', array(), 'p2'),
                    array(
                        'andWhere',
                        array(
                            $this->getExpressionFactory()->gt(
                                self::TEST_ALIAS . '.' . self::TEST_FIELD,
                                ':' . self::TEST_NAME . '_p1'
                            )
                        ),
                        null
                    ),
                    array(
                        'setParameter',
                        array(
                            self::TEST_NAME . '_p1',
                            $this->dateTimeToString(new \DateTime('2012-01-01', new \DateTimeZone('UTC')))
                        ),
                        null
                    ),
                )
            ),
            'date_less_than' => array(
                'data'                  => array(
                    'value' => array('end' => new \DateTime('2012-01-01', new \DateTimeZone('UTC'))),
                    'type'  => DateRangeFilterType::TYPE_LESS_THAN
                ),
                'expectProxyQueryCalls' => array(
                    array('getUniqueParameterId', array(), 'p1'),
                    array('getUniqueParameterId', array(), 'p2'),
                    array(
                        'andWhere',
                        array(
                            $this->getExpressionFactory()->lt(
                                self::TEST_ALIAS . '.' . self::TEST_FIELD,
                                ':' . self::TEST_NAME . '_p2'
                            )
                        ),
                        null
                    ),
                    array(
                        'setParameter',
                        array(
                            self::TEST_NAME . '_p2',
                            $this->dateTimeToString(new \DateTime('2012-01-01', new \DateTimeZone('UTC')))
                        ),
                        null
                    ),
                )
            )
        );
    }

    /**
     * @param \DateTime $dateTime
     * @return string
     */
    protected function dateTimeToString(\DateTime $dateTime)
    {
        return $dateTime->format(AbstractDateFilter::DATETIME_FORMAT);
    }
}
