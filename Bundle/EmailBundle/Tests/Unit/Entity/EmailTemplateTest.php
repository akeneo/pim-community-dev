<?php

namespace Oro\Bundle\EmailBundle\Tests\Unit\Entity;

use Oro\Bundle\EmailBundle\Entity\EmailTemplate;
use Oro\Bundle\EmailBundle\Tests\Unit\Form\Type\EmailTemplateTranslationTypeTest;

use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\OrganizationBundle\Entity\BusinessUnit;
use Oro\Bundle\OrganizationBundle\Entity\Organization;

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
            $this->emailTemplate->setTranslations(array($translation));
            $this->assertEquals($this->emailTemplate->getTranslations(), array($translation));
        }
    }

    /**
     * Test clone, toString
     */
    public function testCloneAndToString()
    {
        $clone = clone $this->emailTemplate;

        $this->assertNull($clone->getId());
        $this->assertEquals($clone->getParent(), $this->emailTemplate->getId());

        $this->assertEquals($this->emailTemplate->getName(), (string)$this->emailTemplate);
    }

    public function testOwners()
    {
        $entity = $this->emailTemplate;
        $user = new User();
        $businessUnit = new BusinessUnit();
        $organization = new Organization();

        $this->assertEmpty($entity->getUserOwner());
        $this->assertEmpty($entity->getBusinessUnitOwner());
        $this->assertEmpty($entity->getOrganizationOwner());

        $entity->setUserOwner($user);
        $entity->setBusinessUnitOwner($businessUnit);
        $entity->setOrganizationOwner($organization);

        $this->assertEquals($user, $entity->getUserOwner());
        $this->assertEquals($businessUnit, $entity->getBusinessUnitOwner());
        $this->assertEquals($organization, $entity->getOrganizationOwner());
    }
}
