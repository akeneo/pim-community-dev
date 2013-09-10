<?php

namespace Oro\Bundle\DataAuditBundle\Tests\Unit\Entity;

use Oro\Bundle\DataAuditBundle\Entity\AuditData;

class AuditDataTest extends \PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        $auditData = new AuditData('key', 'value');

        $this->assertInstanceOf('Oro\Bundle\DataAuditBundle\Entity\AuditData', $auditData);

        $this->assertEquals('key', $auditData->getKey());
        $this->assertEquals('value', $auditData->getValue());
    }
}
