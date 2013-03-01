<?php
namespace Pim\Bundle\ConfigBundle\Tests\Unit\Entity;

use Pim\Bundle\ConfigBundle\Entity\Language;

use Pim\Bundle\ConfigBundle\Entity\Currency;

/**
 * Test related class
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class CurrencyTest extends \PHPUnit_Framework_TestCase
{

    /**
     * Test related method
     */
    public function testConstruct()
    {
        $currency = new Currency();
        $this->assertInstanceOf('Pim\Bundle\ConfigBundle\Entity\Currency', $currency);
    }

    /**
     * Test getter/setter for id property
     */
    public function testGetSetId()
    {
        $currency = new Currency();
        $this->assertEmpty($currency->getId());

        // change value and assert new
        $newId = 5;
        $currency->setId($newId);
        $this->assertEquals($newId, $currency->getId());
    }

    /**
     * Test getter/setter for code property
     */
    public function testGetSetCode()
    {
        $currency = new Currency();
        $this->assertEmpty($currency->getCode());

        // change value and assert new
        $newCode = 'EUR';
        $currency->setCode($newCode);
        $this->assertEquals($newCode, $currency->getCode());
    }

    /**
     * Test getter/setter for activated property
     */
    public function testGetSetActivated()
    {
        $currency = new Currency();
        $this->assertTrue($currency->getActivated());

        // change value and assert new
        $newActivated = false;
        $currency->setActivated($newActivated);
        $this->assertFalse($currency->getActivated());
    }

    /**
     * Test getter/setter for languages property
     */
    public function testGetSetLanguages()
    {
        $currency = new Currency();
        $this->assertInstanceOf('\Doctrine\Common\Collections\ArrayCollection', $currency->getLanguages());
        $this->assertCount(0, $currency->getLanguages());

        // create languages
        $listLanguages = array('fr_FR', 'en_US', 'en_EN');

        $langFr = $this->createLanguage('fr_FR', 'fr_FR');
        $langUs = $this->createLanguage('en_US', 'en_EN');
        $langEn = $this->createLanguage('en_EN', 'en_EN');

        // Set languages and assert
        $newLanguages = array($langFr, $langUs, $langEn);
        $currency->setLanguages(array($langFr, $langUs, $langEn));
        $this->assertInstanceOf('\Doctrine\Common\Collections\ArrayCollection', $currency->getLanguages());
        $this->assertCount(3, $currency->getLanguages());
        foreach ($currency->getLanguages() as $language) {
            $this->assertTrue(in_array($language, $newLanguages));
        }
    }

    /**
     * Create a language for testing
     * @param string $code     Locale code
     * @param string $fallback Fallback code
     *
     * @return \Pim\Bundle\ConfigBundle\Entity\Language
     */
    protected function createLanguage($code, $fallback)
    {
        $language = new Language();
        $language->setCode($code);
        $language->setFallback($fallback);

        return $language;
    }
}
