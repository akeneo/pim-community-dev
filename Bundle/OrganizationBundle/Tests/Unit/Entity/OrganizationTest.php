<?php
namespace Oro\Bundle\OrganizationBundle\Tests\Unit\Entity;

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
}