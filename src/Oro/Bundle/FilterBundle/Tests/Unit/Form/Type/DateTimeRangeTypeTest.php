<?php

namespace Oro\Bundle\FilterBundle\Tests\Unit\Form\Type;

use Oro\Bundle\FilterBundle\Form\Type\DateRangeType;
use Oro\Bundle\FilterBundle\Form\Type\DateTimeRangeType;
use Oro\Bundle\FilterBundle\Tests\Unit\Fixtures\CustomFormExtension;

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
            ->setMethods(['getTimezone'])
            ->getMock();
        $localeSettings->expects($this->any())
            ->method('getTimezone')
            ->will($this->returnValue($this->defaultTimezone));

        $this->formExtensions[] = new CustomFormExtension([new DateRangeType()]);

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
    public function configureOptionsDataProvider()
    {
        return [
            [
                'defaultOptions' => [
                    'field_type'    => 'datetime',
                    'field_options' => [
                        'format'        => 'yyyy-MM-dd HH:mm',
                        'view_timezone' => $this->defaultTimezone
                    ]
                ]
            ]
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function bindDataProvider()
    {
        return [
            'empty' => [
                'bindData' => ['start' => '', 'end' => ''],
                'formData' => ['start' => null, 'end' => null],
                'viewData' => [
                    'value' => ['start' => '', 'end' => ''],
                ],
            ],
            'default timezone' => [
                'bindData' => ['start' => '2012-01-01 13:00', 'end' => '2013-01-01 18:00'],
                'formData' => [
                    'start' => $this->createDateTime('2012-01-01 23:00', 'UTC'),
                    'end'   => $this->createDateTime('2013-01-02 04:00', 'UTC')
                ],
                'viewData' => [
                    'value' => ['start' => '2012-01-01 13:00', 'end' => '2013-01-01 18:00'],
                ],
            ],
            'custom timezone' => [
                'bindData' => ['start' => '2010-06-02T03:04:00-10:00', 'end' => '2013-06-02T03:04:00-10:00'],
                'formData' => [
                    'start' => $this->createDateTime('2010-06-02 03:04', 'America/New_York')
                        ->setTimezone(new \DateTimeZone('America/Los_Angeles')),
                    'end' => $this->createDateTime('2013-06-02 03:04:00', 'America/New_York')
                        ->setTimezone(new \DateTimeZone('America/Los_Angeles')),
                ],
                'viewData' => [
                    'value' => ['start' => '2010-06-02T03:04:00', 'end' => '2013-06-02T03:04:00'],
                ],
                'customOptions' => [
                    'field_options' => [
                        'model_timezone' => 'America/Los_Angeles',
                        'view_timezone'  => 'America/New_York',
                        'format'         => "yyyy-MM-dd'T'HH:mm:ss"
                    ]
                ]
            ],
        ];
    }

    /**
     * Creates date time object from date string
     *
     * @param string $dateString
     * @param string|null $timeZone
     * @param string $format
     * @throws \Exception
     * @return \DateTime
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
