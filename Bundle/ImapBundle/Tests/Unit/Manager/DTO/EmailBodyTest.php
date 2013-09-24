<?php

namespace Oro\Bundle\ImapBundle\Tests\Unit\Manager\DTO;

use Oro\Bundle\ImapBundle\Manager\DTO\EmailBody;

class EmailBodyTest extends \PHPUnit_Framework_TestCase
{
    public function testGettersAndSetters()
    {
        $obj = new EmailBody();
        $obj
            ->setContent('testContent')
            ->setBodyIsText(true);
        $this->assertEquals('testContent', $obj->getContent());
        $this->assertTrue($obj->getBodyIsText());
    }
}
