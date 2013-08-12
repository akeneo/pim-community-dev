<?php

namespace Pim\Bundle\ConfigBundle\Tests\Unit\Helper;

use Pim\Bundle\ConfigBundle\Helper\LocaleHelper;

/**
 * Test related class
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LocaleHelperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var LocaleHelper
     */
    protected $localeHelper;

    /**
     * @var LocaleManager
     */
    protected $localeManager;

    /**
     * Data provider for the localizedLabel method
     * Can only test for a user locale because locale helper use static property
     *
     * @static
     *
     * @return array
     */
    public static function dataProviderForLocalizedLabel()
    {
        return array(
            'FR' => array(
                'fr_FR',
                array(
                    'en_US' => 'anglais (États-Unis)',
                    'en_EN' => 'anglais',
                    'fr_FR' => 'français'
                )
            )
        );
    }

    /**
     * Create locale helper
     *
     * @param string $userLocaleCode
     *
     * @return \Pim\Bundle\ConfigBundle\Helper\LocaleHelper
     */
    protected function createLocaleHelper($userLocaleCode)
    {
        $localeManager = $this->createLocaleManager($userLocaleCode);
        $localeHelper = new LocaleHelper($localeManager);

        return $localeHelper;
    }

    /**
     * Create locale manager
     *
     * @param string $userLocaleCode
     *
     * @return \Pim\Bundle\ConfigBundle\Manager\LocaleManager
     */
    protected function createLocaleManager($userLocaleCode)
    {
        $localeManager = $this->getMockBuilder('Pim\Bundle\ConfigBundle\Manager\LocaleManager')
             ->disableOriginalConstructor()
             ->getMock(array('getUserLocaleCode'));

        $localeManager
            ->expects($this->any())
            ->method('getUserLocaleCode')
            ->will($this->returnValue($userLocaleCode));

        return $localeManager;
    }

    /**
     * Test the method get localized label
     * Can only test for a specific locale because the helper stock locales in static property
     *
     * @dataProvider dataProviderForLocalizedLabel
     *
     * @param string $userLocaleCode
     * @param array  $expectedResults
     */
    public function testGetLocalizedLabelFR($userLocaleCode, $expectedResults)
    {
        $localeHelper = $this->createLocaleHelper($userLocaleCode);

        foreach ($expectedResults as $code => $expectedResult) {
            $result = $localeHelper->getLocalizedLabel($code);
            $this->assertEquals($expectedResult, $localeHelper->getLocalizedLabel($code));
        }
    }
}
