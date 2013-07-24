<?php

namespace Oro\Bundle\ImapBundle\Tests\Unit\Mail\Storage;

use Oro\Bundle\ImapBundle\Mail\Storage\Content;

class ContentTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructorAndGetters()
    {
        $content = 'testContent';
        $contentType = 'testContentType';
        $encoding = 'testEncoding';
        $obj = new Content($content, $contentType, $encoding);

        $this->assertEquals($content, $obj->getContent());
        $this->assertEquals($contentType, $obj->getContentType());
        $this->assertEquals($encoding, $obj->getEncoding());
    }
}
