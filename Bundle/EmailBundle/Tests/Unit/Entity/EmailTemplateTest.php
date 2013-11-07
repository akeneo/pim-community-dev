<?php

namespace Oro\Bundle\EmailBundle\Tests\Unit\Entity;

use Doctrine\Common\Collections\ArrayCollection;

use Oro\Bundle\EmailBundle\Entity\EmailTemplate;

class EmailTemplateTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var EmailTemplate
     */
    protected $emailTemplate;

    public function setUp()
    {
        $this->emailTemplate = new EmailTemplate('update_entity.html.twig', "@subject = sdfdsf\n abc");

        $this->assertEquals('abc', $this->emailTemplate->getContent());
        $this->assertFalse($this->emailTemplate->getIsSystem());
        $this->assertEquals('html', $this->emailTemplate->getType());
    }

    public function tearDown()
    {
        unset($this->emailTemplate);
    }

    /**
     * Test setters, getters
     */
    public function testSettersGetters()
    {
        foreach (array(
                     'name',
                     'isSystem',
                     'parent',
                     'subject',
                     'content',
                     'locale',
                     'entityName',
                     'type',
                 ) as $field) {
            $this->emailTemplate->{'set'.ucfirst($field)}('abc');
            $this->assertEquals('abc', $this->emailTemplate->{'get'.ucfirst($field)}());

            $translation = $this->getMock('Oro\Bundle\EmailBundle\Entity\EmailTemplateTranslation');
            $this->emailTemplate->setTranslations(new ArrayCollection(array($translation)));
            $this->assertInstanceOf(
                'Doctrine\Common\Collections\ArrayCollection',
                $this->emailTemplate->getTranslations()
            );
            $this->assertCount(1, $this->emailTemplate->getTranslations());
        }
    }

    /**
     * Test clone, toString
     */
    public function testCloneAndToString()
    {
        $translation = $this->getMock('Oro\Bundle\EmailBundle\Entity\EmailTemplateTranslation');

        $this->emailTemplate->getTranslations()->add($translation);

        $clone = clone $this->emailTemplate;

        $this->assertNull($clone->getId());
        $this->assertEquals($clone->getParent(), $this->emailTemplate->getId());

        $this->assertFalse($clone->getIsSystem());
        $this->assertTrue($clone->getIsEditable());

        $this->assertEquals($this->emailTemplate->getName(), (string)$this->emailTemplate);
        $this->assertFalse($clone->getTranslations()->first() === $translation);
    }

    public function testCloneForSystemNonEditableTemplate()
    {
        $emailTemplate = new EmailTemplate();
        $emailTemplate->setIsSystem(true);
        $emailTemplate->setIsEditable(false);

        $this->assertTrue($emailTemplate->getIsSystem());
        $this->assertFalse($emailTemplate->getIsEditable());

        $clone = clone $emailTemplate;

        $this->assertFalse($clone->getIsSystem());
        $this->assertTrue($clone->getIsEditable());
    }
}
