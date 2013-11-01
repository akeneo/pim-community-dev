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
    protected $defaultTimezone = 'Pacific/Honolulu';

    protected function setUp()
    {
        $localeSettings = $this->getMockBuilder('Oro\Bundle\LocaleBundle\Model\LocaleSettings')
            ->disableOriginalConstructor()
            ->setMethods(array('getTimezone'))
            ->getMock();
        $localeSettings->expects($this->any())
            ->method('getTimezone')
            ->will($this->returnValue($this->defaultTimezone));

        $this->formExtensions[] = new CustomFormExtension(array(new DateRangeType()));

        parent::setUp();

        $this->type = new DateTimeRangeType($localeSettings);
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
                    'field_options' => array(
                        'format' => 'yyyy-MM-dd HH:mm',
                        'view_timezone' => $this->defaultTimezone
                    )
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
                    'start' => $this->createDateTime('2012-01-01 23:00', 'UTC'),
                    'end' => $this->createDateTime('2013-01-02 04:00', 'UTC')
                ),
                'viewData' => array(
                    'value' => array('start' => '2012-01-01 13:00', 'end' => '2013-01-01 18:00'),
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
        );
    }

    /**
     * Creates date time object from date string
     *
     * @param string $dateString
     * @param string|null $timeZone
     * @param string $format
     * @return \DateTime
     * @throws \Exception
     */
    private function createDateTime(
        $dateString,
        $timeZone = null,
        $format = 'yyyy-MM-dd HH:mm'
    ) {
        $pattern = $format ? $format : null;

        if (!$timeZone) {
            $timeZone = date_default_timezone_get();
        }

        $calendar = \IntlDateFormatter::GREGORIAN;
        $intlDateFormatter = new \IntlDateFormatter(
            \Locale::getDefault(),
            \IntlDateFormatter::NONE,
            \IntlDateFormatter::NONE,
            $timeZone,
            $calendar,
            $pattern
        );
        $intlDateFormatter->setLenient(false);
        $timestamp = $intlDateFormatter->parse($dateString);

        if (intl_get_error_code() != 0) {
            throw new \Exception(intl_get_error_message());
        }

        // read timestamp into DateTime object - the formatter delivers in UTC
        $dateTime = new \DateTime(sprintf('@%s UTC', $timestamp));
        if ('UTC' !== $timeZone) {
            try {
                $dateTime->setTimezone(new \DateTimeZone($timeZone));
            } catch (\Exception $e) {
                throw new \Exception($e->getMessage(), $e->getCode(), $e);
            }
        }

        return $dateTime;
    }
}
