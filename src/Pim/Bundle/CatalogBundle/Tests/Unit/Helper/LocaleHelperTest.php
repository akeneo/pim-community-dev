<?php

namespace Pim\Bundle\CatalogBundle\Tests\Unit\Helper;

use Pim\Bundle\CatalogBundle\Helper\LocaleHelper;

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
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $localeManager = $this->getMockBuilder('Pim\Bundle\CatalogBundle\Manager\LocaleManager')
                ->disableOriginalConstructor()
                ->getMock();
        $securityContext = $this->getMock('Symfony\Component\Security\Core\SecurityContextInterface');
        $this->localeHelper = new LocaleHelper($localeManager, $securityContext, 'en_US');
    }
    /**
     * Data provider for the localeLabel method
     * Can only test for a user locale because locale helper use static property
     *
     * @return array
     */
    public static function dataProviderForLocaleLabel()
    {
        return array(
            'FR' => array(
                'fr_FR',
                array(
                    'en_US' => 'anglais (États-Unis)',
                    'en_EN' => 'anglais (EN)',
                    'fr_FR' => 'français (France)',
                    'de_DE' => 'allemand (Allemagne)',
                    'azert' => 'azert'
                )
            ),
            'EN' => array(
                'en_US',
                array(
                    'en_US' => 'English (United States)',
                    'en_EN' => 'English (EN)',
                    'fr_FR' => 'French (France)',
                    'de_DE' => 'German (Germany)',
                    'azert' => 'azert'
                )
            ),
            'DE' => array(
                'de_DE',
                array(
                    'en_US' => 'Englisch (Vereinigte Staaten)',
                    'en_EN' => 'Englisch (EN)',
                    'fr_FR' => 'Französisch (Frankreich)',
                    'de_DE' => 'Deutsch (Deutschland)',
                    'azert' => 'azert'
                )
            )
        );
    }

    /**
     * Test the method get localized label
     * Can only test for a specific locale because the helper stock locales in static property
     *
     * @param string $userLocaleCode
     * @param array  $expectedResults
     *
     * @dataProvider dataProviderForLocaleLabel
     */
    public function testGetLocaleLabelFR($userLocaleCode, $expectedResults)
    {
        foreach ($expectedResults as $code => $expectedResult) {
            $this->assertEquals($expectedResult, $this->localeHelper->getLocaleLabel($code, $userLocaleCode));
        }
    }
}
