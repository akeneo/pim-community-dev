<?php
namespace Pim\Bundle\ProductBundle\Tests\Unit\Entity;

use Pim\Bundle\ProductBundle\Entity\ExportProfileTranslation;

use Pim\Bundle\ProductBundle\Entity\ExportProfile;

/**
 * Test related class
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class ExportProfileTest extends \PHPUnit_Framework_TestCase
{

    /**
     * Test related method
     */
    public function testConstruct()
    {
        $exportProfile = new ExportProfile();

        // assert instance and implementation
        $this->assertEntity($exportProfile);
        $this->assertInstanceOf('\Pim\Bundle\TranslationBundle\Entity\TranslatableInterface', $exportProfile);

        // assert object properties
        $this->assertInstanceOf('\Doctrine\Common\Collections\Collection', $exportProfile->getTranslations());
        $this->assertCount(0, $exportProfile->getTranslations());
    }

    /**
     * Test getter/setter for id property
     */
    public function testId()
    {
        $exportProfile = new ExportProfile();
        $this->assertEmpty($exportProfile->getId());
    }

    /**
     * Test getter/setter for code property
     */
    public function testCode()
    {
        $exportProfile = new ExportProfile();
        $this->assertEmpty($exportProfile->getCode());

        // Change value and assert new
        $newCode = 'test-code';
        $this->assertEntity($exportProfile->setCode($newCode));
        $this->assertEquals($newCode, $exportProfile->getCode());
    }

    /**
     * Test getter/setter for name property
     */
    public function testName()
    {
        $exportProfile = new ExportProfile();
        $this->assertEmpty($exportProfile->getName());

        // Change value and assert new
        $newName = 'test-name';
        $this->assertEntity($exportProfile->setName($newName));
        $this->assertEquals($newName, $exportProfile->getName());
    }

    /**
     * Test getter/setter for translations property
     */
    public function testTranslations()
    {
        $exportProfile = new ExportProfile();
        $this->assertCount(0, $exportProfile->getTranslations());

        // Change value and assert new
        $newTranslation = new ExportProfileTranslation();
        $this->assertEntity($exportProfile->addTranslation($newTranslation));
        $this->assertCount(1, $exportProfile->getTranslations());
        $this->assertInstanceOf(
            'Pim\Bundle\ProductBundle\Entity\ExportProfileTranslation',
            $exportProfile->getTranslations()->first()
        );

        $exportProfile->addTranslation($newTranslation);
        $this->assertCount(1, $exportProfile->getTranslations());

        $this->assertEntity($exportProfile->removeTranslation($newTranslation));
        $this->assertCount(0, $exportProfile->getTranslations());
    }

    /**
     * Test related method
     * Just a call to prevent fatal errors (no way to verify value is set)
     */
    public function testLocale()
    {
        $exportProfile = new ExportProfile();
        $exportProfile->setLocale('en_US');
    }

    /**
     * Assert entity
     * @param Pim\Bundle\ProductBundle\Entity\AttributeGroup $entity
     */
    protected function assertEntity($entity)
    {
        $this->assertInstanceOf('Pim\Bundle\ProductBundle\Entity\ExportProfile', $entity);
    }
}
