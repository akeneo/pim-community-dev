<?php

namespace Oro\Bundle\ImapBundle\Tests\Unit\Mail\Storage;

use Oro\Bundle\ImapBundle\Mail\Storage\Content;

class ContentTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructorAndGetters()
    {
        $content = 'testContent';
        $contentType = 'testContentType';
        $contentTransferEncoding = 'testContentTransferEncoding';
        $encoding = 'testEncoding';
        $obj = new Content($content, $contentType, $contentTransferEncoding, $encoding);

        $this->assertEquals($content, $obj->getContent());
        $this->assertEquals($contentType, $obj->getContentType());
        $this->assertEquals($contentTransferEncoding, $obj->getContentTransferEncoding());
        $this->assertEquals($encoding, $obj->getEncoding());
    }
}
