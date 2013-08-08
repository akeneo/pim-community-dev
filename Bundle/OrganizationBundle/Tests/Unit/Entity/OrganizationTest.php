<?php
namespace Oro\Bundle\OrganizationBundle\Tests\Unit\Entity;

use Doctrine\Common\Collections\ArrayCollection;

use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\OrganizationBundle\Entity\BusinessUnit;
use Oro\Bundle\OrganizationBundle\Entity\Organization;

class OrganizationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Organization
     */
    protected $organization;

    public function setUp()
    {
        $this->organization = new Organization();
    }

    public function testName()
    {
        $name = 'testName';
        $this->assertNull($this->organization->getName());
        $this->organization->setName($name);
        $this->assertEquals($name, $this->organization->getName());
        $this->assertEquals($name, (string)$this->organization);
    }

    public function testId()
    {
        $this->assertNull($this->organization->getId());
    }

    public function testCurrency()
    {
        $currency = 'USD';
        $this->assertNull($this->organization->getCurrency());
        $this->organization->setCurrency($currency);
        $this->assertEquals($currency, $this->organization->getCurrency());
    }

    public function testPrecision()
    {
        $precision = '000 000.00';
        $this->assertNull($this->organization->getPrecision());
        $this->organization->setPrecision($precision);
        $this->assertEquals($precision, $this->organization->getPrecision());
    }

    public function testOwners()
    {
        $entity = $this->organization;
        $user = new User();
        $businessUnits = new ArrayCollection(array(new BusinessUnit()));
        $organizations = new ArrayCollection(array(new Organization()));

        $this->assertEmpty($entity->getUserOwner());
        $this->assertEmpty($entity->getBusinessUnitOwners());
        $this->assertEmpty($entity->getOrganizationOwners());

        $entity->setUserOwner($user);
        $entity->setBusinessUnitOwners($businessUnits);
        $entity->setOrganizationOwners($organizations);

        $this->assertEquals($user, $entity->getUserOwner());
        $this->assertEquals($businessUnits, $entity->getBusinessUnitOwners());
        $this->assertEquals($organizations, $entity->getOrganizationOwners());
    }
}
