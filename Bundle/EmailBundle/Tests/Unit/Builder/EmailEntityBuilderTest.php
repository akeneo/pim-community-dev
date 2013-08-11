<?php

namespace Oro\Bundle\EmailBundle\Tests\Unit\Builder;

use Oro\Bundle\EmailBundle\Builder\EmailEntityBuilder;
use Oro\Bundle\EmailBundle\Entity\Manager\EmailAddressManager;

class EmailEntityBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var EmailEntityBuilder
     */
    private $builder;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $batch;

    protected function setUp()
    {
        $this->batch = $this->getMockBuilder('Oro\Bundle\EmailBundle\Builder\EmailEntityBatchCooker')
            ->disableOriginalConstructor()
            ->getMock();
        $addrManager = new EmailAddressManager('Oro\Bundle\EmailBundle\Tests\Unit\Entity\TestFixtures', 'Test%sProxy');
        $this->builder = new EmailEntityBuilder($this->batch, $addrManager);
    }

    public function testAttachment()
    {
        $attachment = $this->builder->attachment('testFileName', 'testContentType');

        $this->assertEquals('testFileName', $attachment->getFileName());
        $this->assertEquals('testContentType', $attachment->getContentType());
    }
}
