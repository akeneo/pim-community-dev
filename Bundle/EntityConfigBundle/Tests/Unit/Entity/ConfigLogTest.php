<?php

namespace Oro\Bundle\EntityConfigBundle\Tests\Unit\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Oro\Bundle\EntityConfigBundle\Entity\ConfigLog;
use Oro\Bundle\EntityConfigBundle\Entity\ConfigLogDiff;

class ConfigLogTest extends \PHPUnit_Framework_TestCase
{
    /** @var  ConfigLog */
    private $configLog;

    /** @var  ConfigLogDiff */
    private $configLogDiff;

    protected function setUp()
    {
        $this->configLog     = new ConfigLog();
        $this->configLogDiff = new ConfigLogDiff();
    }

    protected function tearDown()
    {
        $this->configLog     = null;
        $this->configLogDiff = null;
    }

    public function testConfigLog()
    {
        $userMock = $this->getMockForAbstractClass('Symfony\Component\Security\Core\User\UserInterface');

        $this->assertEmpty($this->configLog->getId());

        $data = new \DateTime();
        $this->configLog->setLoggedAt($data);
        $this->assertEquals($data, $this->configLog->getLoggedAt());

        $this->configLog->setLoggedAt(null);
        $this->configLog->prePersist();
        $this->assertInstanceOf('\DateTime', $this->configLog->getLoggedAt());

        $this->configLog->setUser($userMock);
        $this->assertEquals($userMock, $this->configLog->getUser());

        $this->configLog->addDiff($this->configLogDiff);
        $this->assertEquals($this->configLogDiff, $this->configLog->getDiffs()->first());

        $diffsCollection = new ArrayCollection(array($this->configLogDiff));
        $this->configLog->setDiffs($diffsCollection);
        $this->assertEquals($diffsCollection, $this->configLog->getDiffs());
    }

    public function testConfigDiff()
    {
        $this->assertEmpty($this->configLogDiff->getId());

        $this->configLogDiff->setLog($this->configLog);
        $this->assertEquals($this->configLog, $this->configLogDiff->getLog());

        $this->configLogDiff->setClassName('className');
        $this->assertEquals('className', $this->configLogDiff->getClassName());

        $this->configLogDiff->setFieldName('fieldName');
        $this->assertEquals('fieldName', $this->configLogDiff->getFieldName());

        $this->configLogDiff->setScope('scope');
        $this->assertEquals('scope', $this->configLogDiff->getScope());

        $this->configLogDiff->setDiff(array('key' => 'value'));
        $this->assertEquals(array('key' => 'value'), $this->configLogDiff->getDiff());
    }
}
