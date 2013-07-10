<?php

namespace Oro\Bundle\FilterBundle\Tests\Unit\Form\Type;

use Oro\Bundle\FilterBundle\Tests\Unit\Fixtures\CustomFormExtension;
use Oro\Bundle\FilterBundle\Form\Type\DateRangeType;
use Oro\Bundle\FilterBundle\Form\Type\DateTimeRangeType;

class DateTimeRangeTypeTest extends AbstractTypeTestCase
{
    /**
     * @var DateRangeType
     */
    private $type;

    /**
     * @var string
     */
    protected $defaultLocale = 'en';

    /**
     * @var string
     */
    protected $defaultTimezone = 'Europe/Kiev';

    protected function setUp()
    {
        $this->formExtensions[] = new CustomFormExtension(array(new DateRangeType()));

        parent::setUp();
        $this->type = new DateTimeRangeType();
    }

    /**
     * {@inheritDoc}
     */
    protected function getTestFormType()
    {
        return $this->type;
    }

    public function testGetName()
    {
        $this->assertEquals(DateTimeRangeType::NAME, $this->type->getName());
    }

    /**
     * {@inheritDoc}
     */
    public function setDefaultOptionsDataProvider()
    {
        return array(
            array(
                'defaultOptions' => array(
                    'field_type' => 'datetime',
                )
            )
        );
    }

    /**
     * {@inheritDoc}
     */
    public function bindDataProvider()
    {
        return array(
            'empty' => array(
                'bindData' => array('start' => '', 'end' => ''),
                'formData' => array('start' => null, 'end' => null),
                'viewData' => array(
                    'value' => array('start' => '', 'end' => ''),
                ),
            ),
            'default timezone' => array(
                'bindData' => array('start' => '2012-01-01 13:00', 'end' => '2013-01-01 18:00'),
                'formData' => array(
                    'start' => $this->createDateTime('2012-01-01 13:00'),
                    'end' => $this->createDateTime('2013-01-01 18:00')
                ),
                'viewData' => array(
                    'value' => array('start' => '2012-01-01T13:00:00+02:00', 'end' => '2013-01-01T18:00:00+02:00'),
                ),
            ),
            'custom timezone' => array(
                'bindData' => array('start' => '2010-06-02T03:04:00-10:00', 'end' => '2013-06-02T03:04:00-10:00'),
                'formData' => array(
                    'start' => $this->createDateTime('2010-06-02 03:04', 'America/New_York')
                        ->setTimezone(new \DateTimeZone('America/Los_Angeles')),
                    'end' => $this->createDateTime('2013-06-02 03:04:00', 'America/New_York')
                        ->setTimezone(new \DateTimeZone('America/Los_Angeles')),
                ),
                'viewData' => array(
                    'value' => array('start' => '2010-06-02T03:04:00', 'end' => '2013-06-02T03:04:00'),
                ),
                'customOptions' => array(
                    'field_options' => array(
                        'model_timezone' => 'America/Los_Angeles',
                        'view_timezone' => 'America/New_York',
                        'format' => "yyyy-MM-dd'T'HH:mm:ss"
                    )
                )
            ),
            'without time' => array(
                'bindData' => array('start' => '1/13/1970', 'end' => '1/13/2014'),
                'formData' => array(
                    'start' => $this->createDateTime('1970-01-13 00:00:00'),
                    'end' => $this->createDateTime('2014-01-13 00:00:00')
                ),
                'viewData' => array(
                    'value' => array('start' => '1970-01-13T00:00:00+03:00', 'end' => '2014-01-13T00:00:00+02:00'),
                ),
                'customOptions' => array()
            ),
        );
    }

    /**
     * Creates date time object from date string
     *
     * @param string $dateString
     * @param string|null $timeZone
     * @return \DateTime
     */
    private function createDateTime(
        $dateString,
        $timeZone = null
    ) {
        if (!$timeZone) {
            $timeZone = $this->defaultTimezone ? $this->defaultTimezone : date_default_timezone_get();
        }

        return new \DateTime($dateString, new \DateTimeZone($timeZone));
    }
}
