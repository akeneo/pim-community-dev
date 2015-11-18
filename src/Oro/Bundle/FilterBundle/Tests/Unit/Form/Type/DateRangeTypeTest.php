<?php

namespace Oro\Bundle\FilterBundle\Tests\Unit\Form\Type;

use Oro\Bundle\FilterBundle\Form\Type\DateRangeType;

class DateRangeTypeTest extends AbstractTypeTestCase
{
    /**
     * @var DateRangeType
     */
    private $type;

    /**
     * @var string
     */
    protected $defaultLocale = 'en';

    protected function setUp()
    {
        parent::setUp();

        $this->type = new DateRangeType();
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
        $this->assertEquals(DateRangeType::NAME, $this->type->getName());
    }

    /**
     * {@inheritDoc}
     */
    public function configureOptionsDataProvider()
    {
        return [
            [
                'defaultOptions' => [
                    'field_type'          => 'date',
                    'field_options'       => [],
                    'start_field_options' => [],
                    'end_field_options'   => [],
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
            'custom format' => [
                'bindData' => ['start' => 'Jan 12, 1970', 'end' => 'Jan 12, 2013'],
                'formData' => [
                    'start' => $this->createDateTime('1970-01-12', 'UTC'),
                    'end'   => $this->createDateTime('2013-01-12', 'UTC'),
                ],
                'viewData' => [
                    'value' => ['start' => 'Jan 12, 1970', 'end' => 'Jan 12, 2013'],
                ],
                'customOptions' => [
                    'field_options' => [
                        'format' => \IntlDateFormatter::MEDIUM
                    ]
                ]
            ]
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
        $format = 'yyyy-MM-dd'
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
