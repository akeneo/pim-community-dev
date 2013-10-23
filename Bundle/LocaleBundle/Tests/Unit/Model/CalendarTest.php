<?php

namespace Oro\Bundle\LocaleBundle\Tests\Unit\Model;

use Oro\Bundle\LocaleBundle\Model\Calendar;

class CalendarTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $localeSettings;

    /**
     * @var Calendar
     */
    protected $calendar;

    protected function setUp()
    {
        $this->localeSettings = $this->getMockBuilder('Oro\Bundle\LocaleBundle\Model\LocaleSettings')
            ->disableOriginalConstructor()
            ->getMock();

        $this->calendar = new Calendar($this->localeSettings);
    }

    /**
     * @dataProvider getFirstDayOfWeekDataProvider
     */
    public function testGetFirstDayOfWeek($locale, $expected, $defaultLocale = null)
    {
        if (null !== $defaultLocale) {
            $this->localeSettings->expects($this->once())
                ->method('getLocale')
                ->will($this->returnValue($defaultLocale));
        }
        $this->assertEquals($expected, $this->calendar->getFirstDayOfWeek($locale));
    }

    public function getFirstDayOfWeekDataProvider()
    {
        return array(
            'en_US, Sunday, Default locale' => array(null, Calendar::DOW_SUNDAY, 'en_US'),
            'en_US, Sunday' => array('en_US', Calendar::DOW_SUNDAY),
            'fr_CA, Sunday' => array('fr_CA', Calendar::DOW_SUNDAY),
            'he_IL, Sunday' => array('he_IL', Calendar::DOW_SUNDAY),
            'ar_SA, Sunday' => array('ar_SA', Calendar::DOW_SUNDAY),
            'ko_KR, Sunday' => array('ko_KR', Calendar::DOW_SUNDAY),
            'lo_LA, Sunday' => array('lo_LA', Calendar::DOW_SUNDAY),
            'ja_JP, Sunday' => array('ja_JP', Calendar::DOW_SUNDAY),
            'id_ID, Sunday' => array('id_ID', Calendar::DOW_SUNDAY),
            'hi_IN, Sunday' => array('hi_IN', Calendar::DOW_SUNDAY),
            'kn_IN, Sunday' => array('kn_IN', Calendar::DOW_SUNDAY),
            'zh_CN, Sunday' => array('zh_CN', Calendar::DOW_SUNDAY),
            'ru_RU, Monday' => array('ru_RU', Calendar::DOW_MONDAY),
            'en_GB, Monday' => array('en_GB', Calendar::DOW_MONDAY),
            'sq_AL, Monday' => array('sq_AL', Calendar::DOW_MONDAY),
            'bg_BG, Monday' => array('bg_BG', Calendar::DOW_MONDAY),
            'vi_VN, Monday' => array('vi_VN', Calendar::DOW_MONDAY),
            'it_IT, Monday' => array('it_IT', Calendar::DOW_MONDAY),
            'fr_FR, Monday' => array('fr_FR', Calendar::DOW_MONDAY),
            'eu_ES, Monday' => array('eu_ES', Calendar::DOW_MONDAY),
            'is_IS, Monday' => array('is_IS', Calendar::DOW_MONDAY),
            'ka_GE, Monday' => array('ka_GE', Calendar::DOW_MONDAY),
        );
    }

    /**
     * @dataProvider getMonthNamesDataProvider
     */
    public function testGetMonthNames($locale, array $expected, $defaultLocale = null)
    {
        if (null !== $defaultLocale) {
            $this->localeSettings->expects($this->once())
                ->method('getLocale')
                ->will($this->returnValue($defaultLocale));
        }
        $this->assertEquals($expected, $this->calendar->getMonthNames($locale));
    }

    public function getMonthNamesDataProvider()
    {
        return array(
            'en_US' => array(
                'en_US',
                array(
                    1 => 'January',
                    2 => 'February',
                    3 => 'March',
                    4 => 'April',
                    5 => 'May',
                    6 => 'June',
                    7 => 'July',
                    8 => 'August',
                    9 => 'September',
                    10 => 'October',
                    11 => 'November',
                    12 => 'December',
                )
            ),
            'en_US, default locale' => array(
                null,
                array(
                    1 => 'January',
                    2 => 'February',
                    3 => 'March',
                    4 => 'April',
                    5 => 'May',
                    6 => 'June',
                    7 => 'July',
                    8 => 'August',
                    9 => 'September',
                    10 => 'October',
                    11 => 'November',
                    12 => 'December',
                ),
                'en_US'
            ),
            'it_IT' => array(
                'it_IT',
                array(
                    1 => 'Gennaio',
                    2 => 'Febbraio',
                    3 => 'Marzo',
                    4 => 'Aprile',
                    5 => 'Maggio',
                    6 => 'Giugno',
                    7 => 'Luglio',
                    8 => 'Agosto',
                    9 => 'Settembre',
                    10 => 'Ottobre',
                    11 => 'Novembre',
                    12 => 'Dicembre',
                )
            ),
            'id_ID' => array(
                'id_ID',
                array(
                    1 => 'Januari',
                    2 => 'Februari',
                    3 => 'Maret',
                    4 => 'April',
                    5 => 'Mei',
                    6 => 'Juni',
                    7 => 'Juli',
                    8 => 'Agustus',
                    9 => 'September',
                    10 => 'Oktober',
                    11 => 'November',
                    12 => 'Desember',
                )
            ),
        );
    }

    /**
     * @dataProvider getMonthShortNamesDataProvider
     */
    public function testGetMonthShortNames($locale, array $expected, $defaultLocale = null)
    {
        if (null !== $defaultLocale) {
            $this->localeSettings->expects($this->once())
                ->method('getLocale')
                ->will($this->returnValue($defaultLocale));
        }
        $this->assertEquals($expected, $this->calendar->getMonthShortNames($locale));
    }

    public function getMonthShortNamesDataProvider()
    {
        return array(
            'en_US' => array(
                'en_US',
                array(
                    1 => 'Jan',
                    2 => 'Feb',
                    3 => 'Mar',
                    4 => 'Apr',
                    5 => 'May',
                    6 => 'Jun',
                    7 => 'Jul',
                    8 => 'Aug',
                    9 => 'Sep',
                    10 => 'Oct',
                    11 => 'Nov',
                    12 => 'Dec',
                )
            ),
            'en_US, default locale' => array(
                null,
                array(
                    1 => 'Jan',
                    2 => 'Feb',
                    3 => 'Mar',
                    4 => 'Apr',
                    5 => 'May',
                    6 => 'Jun',
                    7 => 'Jul',
                    8 => 'Aug',
                    9 => 'Sep',
                    10 => 'Oct',
                    11 => 'Nov',
                    12 => 'Dec',
                ),
                'en_US'
            ),
            'it_IT' => array(
                'it_IT',
                array(
                    1 => 'gen',
                    2 => 'feb',
                    3 => 'mar',
                    4 => 'apr',
                    5 => 'mag',
                    6 => 'giu',
                    7 => 'lug',
                    8 => 'ago',
                    9 => 'set',
                    10 => 'ott',
                    11 => 'nov',
                    12 => 'dic',
                )
            ),
            'id_ID' => array(
                'id_ID',
                array(
                    1 => 'Jan',
                    2 => 'Feb',
                    3 => 'Mar',
                    4 => 'Apr',
                    5 => 'Mei',
                    6 => 'Jun',
                    7 => 'Jul',
                    8 => 'Agt',
                    9 => 'Sep',
                    10 => 'Okt',
                    11 => 'Nov',
                    12 => 'Des',
                )
            ),
        );
    }

    /**
     * @dataProvider getDayNamesDataProvider
     */
    public function testGetDayNames($locale, array $expected, $defaultLocale = null)
    {
        if (null !== $defaultLocale) {
            $this->localeSettings->expects($this->once())
                ->method('getLocale')
                ->will($this->returnValue($defaultLocale));
        }
        $this->assertEquals($expected, $this->calendar->getDayNames($locale));
    }

    public function getDayNamesDataProvider()
    {
        return array(
            'en_US' => array(
                'en_US',
                array(
                    Calendar::DOW_SUNDAY => 'Sunday',
                    Calendar::DOW_MONDAY => 'Monday',
                    Calendar::DOW_TUESDAY => 'Tuesday',
                    Calendar::DOW_WEDNESDAY => 'Wednesday',
                    Calendar::DOW_THURSDAY => 'Thursday',
                    Calendar::DOW_FRIDAY => 'Friday',
                    Calendar::DOW_SATURDAY => 'Saturday',
                )
            ),
            'en_US, default locale' => array(
                null,
                array(
                    Calendar::DOW_SUNDAY => 'Sunday',
                    Calendar::DOW_MONDAY => 'Monday',
                    Calendar::DOW_TUESDAY => 'Tuesday',
                    Calendar::DOW_WEDNESDAY => 'Wednesday',
                    Calendar::DOW_THURSDAY => 'Thursday',
                    Calendar::DOW_FRIDAY => 'Friday',
                    Calendar::DOW_SATURDAY => 'Saturday',
                ),
                'en_US'
            ),
            'fr_FR' => array(
                null,
                array(
                    Calendar::DOW_SUNDAY => 'dimanche',
                    Calendar::DOW_MONDAY => 'lundi',
                    Calendar::DOW_TUESDAY => 'mardi',
                    Calendar::DOW_WEDNESDAY => 'mercredi',
                    Calendar::DOW_THURSDAY => 'jeudi',
                    Calendar::DOW_FRIDAY => 'vendredi',
                    Calendar::DOW_SATURDAY => 'samedi',
                ),
                'fr_FR'
            ),
            'ru_RU' => array(
                null,
                array(
                    Calendar::DOW_SUNDAY => 'Воскресенье',
                    Calendar::DOW_MONDAY => 'Понедельник',
                    Calendar::DOW_TUESDAY => 'Вторник',
                    Calendar::DOW_WEDNESDAY => 'Среда',
                    Calendar::DOW_THURSDAY => 'Четверг',
                    Calendar::DOW_FRIDAY => 'Пятница',
                    Calendar::DOW_SATURDAY => 'Суббота',
                ),
                'ru_RU'
            ),
        );
    }

    /**
     * @dataProvider getDayShortNamesDataProvider
     */
    public function testGetDayShortNames($locale, array $expected, $defaultLocale = null)
    {
        if (null !== $defaultLocale) {
            $this->localeSettings->expects($this->once())
                ->method('getLocale')
                ->will($this->returnValue($defaultLocale));
        }
        $this->assertEquals($expected, $this->calendar->getDayShortNames($locale));
    }

    public function getDayShortNamesDataProvider()
    {
        return array(
            'en_US' => array(
                'en_US',
                array(
                    Calendar::DOW_SUNDAY    => 'Sun',
                    Calendar::DOW_MONDAY    => 'Mon',
                    Calendar::DOW_TUESDAY   => 'Tue',
                    Calendar::DOW_WEDNESDAY => 'Wed',
                    Calendar::DOW_THURSDAY  => 'Thu',
                    Calendar::DOW_FRIDAY    => 'Fri',
                    Calendar::DOW_SATURDAY  => 'Sat',
                )
            ),
            'en_US, default locale' => array(
                null,
                array(
                    Calendar::DOW_SUNDAY    => 'Sun',
                    Calendar::DOW_MONDAY    => 'Mon',
                    Calendar::DOW_TUESDAY   => 'Tue',
                    Calendar::DOW_WEDNESDAY => 'Wed',
                    Calendar::DOW_THURSDAY  => 'Thu',
                    Calendar::DOW_FRIDAY    => 'Fri',
                    Calendar::DOW_SATURDAY  => 'Sat',
                ),
                'en_US'
            ),
            'fr_FR' => array(
                null,
                array(
                    Calendar::DOW_SUNDAY    => 'dim.',
                    Calendar::DOW_MONDAY    => 'lun.',
                    Calendar::DOW_TUESDAY   => 'mar.',
                    Calendar::DOW_WEDNESDAY => 'mer.',
                    Calendar::DOW_THURSDAY  => 'jeu.',
                    Calendar::DOW_FRIDAY    => 'ven.',
                    Calendar::DOW_SATURDAY  => 'sam.',
                ),
                'fr_FR'
            ),
            'ru_RU' => array(
                null,
                array(
                    Calendar::DOW_SUNDAY    => 'Вс',
                    Calendar::DOW_MONDAY    => 'Пн',
                    Calendar::DOW_TUESDAY   => 'Вт',
                    Calendar::DOW_WEDNESDAY => 'Ср',
                    Calendar::DOW_THURSDAY  => 'Чт',
                    Calendar::DOW_FRIDAY    => 'Пт',
                    Calendar::DOW_SATURDAY  => 'Сб',
                ),
                'ru_RU'
            ),
        );
    }
}
