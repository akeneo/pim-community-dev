<?php
namespace Pim\Bundle\ConfigBundle\Tests\Unit\Entity;

use Pim\Bundle\ConfigBundle\Entity\Language;

/**
 * Test related class
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class LanguageTest extends \PHPUnit_Framework_TestCase
{

    /**
     * Test related method
     */
    public function testConstruct()
    {
        $language = new Language();
        $this->assertInstanceOf('Pim\Bundle\ConfigBundle\Entity\Language', $language);
    }

    /**
     * Test getter/setter for id property
     */
    public function testGetSetId()
    {
        $language = new Language();
        $this->assertEmpty($language->getId());

        // change value and assert new
        $newId = 5;
        $language->setId($newId);
        $this->assertEquals($newId, $language->getId());
    }

    /**
     * Test getter/setter for code property
     */
    public function testGetSetCode()
    {
        $language = new Language();
        $this->assertEmpty($language->getCode());

        // change value and assert new
        $newCode = 'fr_FR';
        $language->setCode($newCode);
        $this->assertEquals($newCode, $language->getCode());
    }

    /**
     * Test getter/setter for activated property
     */
    public function testGetSetActivated()
    {
        $language = new Language();
        $this->assertTrue($language->getActivated());

        // change value and assert new
        $newActivated = false;
        $language->setActivated($newActivated);
        $this->assertFalse($language->getActivated());
    }
}
