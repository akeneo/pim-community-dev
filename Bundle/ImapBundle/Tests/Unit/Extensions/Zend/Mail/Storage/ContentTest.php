<?php

namespace Oro\Bundle\ImapBundle\Tests\Unit\Extensions\Zend\Mail\Storage;

use Oro\Bundle\ImapBundle\Extensions\Zend\Mail\Storage\Content;

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
