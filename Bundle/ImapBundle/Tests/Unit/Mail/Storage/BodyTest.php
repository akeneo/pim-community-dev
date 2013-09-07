<?php

namespace Oro\Bundle\ImapBundle\Tests\Unit\Mail\Storage;

use Oro\Bundle\ImapBundle\Mail\Storage\Body;
use Oro\Bundle\ImapBundle\Mail\Storage\Content;

class BodyTest extends \PHPUnit_Framework_TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $part;

    /** @var Body */
    private $body;

    protected function setUp()
    {
        $this->part = $this->getMockBuilder('Zend\Mail\Storage\Part')
            ->disableOriginalConstructor()
            ->getMock();

        $this->body = new Body($this->part);
    }

    public function testGetHeaders()
    {
        $headers = new \stdClass();

        $this->part
            ->expects($this->once())
            ->method('getHeaders')
            ->will($this->returnValue($headers));

        $result = $this->body->getHeaders();

        $this->assertTrue($headers === $result);
    }

    public function testGetHeader()
    {
        $header = new \stdClass();

        $this->part
            ->expects($this->once())
            ->method('getHeader')
            ->with($this->equalTo('SomeHeader'), $this->equalTo('string'))
            ->will($this->returnValue($header));

        $result = $this->body->getHeader('SomeHeader', 'string');

        $this->assertTrue($header === $result);
    }

    public function testGetPartContentType()
    {
        $headers = $this->getMockBuilder('Zend\Mail\Headers')
            ->disableOriginalConstructor()
            ->getMock();

        $headers->expects($this->once())
            ->method('has')
            ->with($this->equalTo('Content-Type'))
            ->will($this->returnValue(true));

        $this->part->expects($this->once())
            ->method('getHeaders')
            ->will($this->returnValue($headers));

        $header = new \stdClass();

        $this->part
            ->expects($this->once())
            ->method('getHeader')
            ->with($this->equalTo('Content-Type'))
            ->will($this->returnValue($header));

        $result = $this->callProtectedMethod($this->body, 'getPartContentType', array($this->part));

        $this->assertTrue($header === $result);
    }

    public function testGetPartContentTypeWithNoContentTypeHeader()
    {
        $headers = $this->getMockBuilder('Zend\Mail\Headers')
            ->disableOriginalConstructor()
            ->getMock();
        $headers->expects($this->once())
            ->method('has')
            ->with($this->equalTo('Content-Type'))
            ->will($this->returnValue(false));

        $this->part->expects($this->once())
            ->method('getHeaders')
            ->will($this->returnValue($headers));


        $result = $this->callProtectedMethod($this->body, 'getPartContentType', array($this->part));

        $this->assertNull($result);
    }

    /**
     * @dataProvider extractContentProvider
     */
    public function testExtractContent(
        $contentTransferEncoding,
        $contentType,
        $contentCharset,
        $contentValue,
        $expected
    ) {
        // Content-Type header
        $contentTypeHeader = $this->getMockBuilder('Zend\Mail\Header\ContentType')
            ->disableOriginalConstructor()
            ->getMock();
        if ($contentType !== null) {
            $contentTypeHeader->expects($this->once())
                ->method('getType')
                ->will($this->returnValue($contentType));
            $contentTypeHeader->expects($this->once())
                ->method('getParameter')
                ->with($this->equalTo('charset'))
                ->will($this->returnValue($contentCharset));
        }

        // Content-Transfer-Encoding header
        $contentTransferEncodingHeader = $this->getMockBuilder('Zend\Mail\Header\GenericHeader')
            ->disableOriginalConstructor()
            ->getMock();
        if ($contentTransferEncoding !== null) {
            $contentTransferEncodingHeader->expects($this->once())
                ->method('getFieldValue')
                ->will($this->returnValue($contentTransferEncoding));
        }

        // Headers object
        $headers = $this->getMockBuilder('Zend\Mail\Headers')
            ->disableOriginalConstructor()
            ->getMock();
        $headers->expects($this->any())
            ->method('has')
            ->will(
                $this->returnValueMap(
                    array(
                        array('Content-Type', $contentType !== null),
                        array('Content-Transfer-Encoding', $contentTransferEncoding !== null),
                    )
                )
            );

        // Part object
        $this->part->expects($this->any())
            ->method('getHeaders')
            ->will($this->returnValue($headers));
        $this->part
            ->expects($this->any())
            ->method('getHeader')
            ->will(
                $this->returnValueMap(
                    array(
                        array('Content-Type', null, $contentTypeHeader),
                        array('Content-Transfer-Encoding', null, $contentTransferEncodingHeader),
                    )
                )
            );
        $this->part->expects($this->once())
            ->method('getContent')
            ->will($this->returnValue($contentValue));

        $result = $this->callProtectedMethod($this->body, 'extractContent', array($this->part));

        $this->assertEquals($expected, $result);
    }

    public function testGetContentSinglePartText()
    {
        $contentValue = 'testContent';
        $contentType = 'testContentType';
        $contentTransferEncoding = 'testContentTransferEncoding';
        $contentEncoding = 'testEncoding';

        $bodyPartialMock = $this->getMock(
            'Oro\Bundle\ImapBundle\Mail\Storage\Body',
            array('extractContent'),
            array($this->part)
        );
        $bodyPartialMock->expects($this->once())
            ->method('extractContent')
            ->will(
                $this->returnValue(
                    new Content($contentValue, $contentType, $contentTransferEncoding, $contentEncoding)
                )
            );

        $this->part->expects($this->once())
            ->method('isMultipart')
            ->will($this->returnValue(false));


        $result = $bodyPartialMock->getContent(Body::FORMAT_TEXT);

        $expected = new Content($contentValue, $contentType, $contentTransferEncoding, $contentEncoding);

        $this->assertEquals($expected, $result);
    }

    /**
     * @expectedException \Oro\Bundle\ImapBundle\Mail\Storage\Exception\InvalidBodyFormatException
     */
    public function testGetContentSinglePartHtml()
    {
        $this->part->expects($this->once())
            ->method('isMultipart')
            ->will($this->returnValue(false));

        $this->body->getContent(Body::FORMAT_HTML);
    }

    public function testGetContentMultipartText()
    {
        $maxIterationCount = 3;
        $iteratorPos = 0;

        $contentTypeHeader = $this->getMockBuilder('Zend\Mail\Header\ContentType')
            ->disableOriginalConstructor()
            ->getMock();
        $contentTypeHeader->expects($this->any())
            ->method('getType')
            ->will(
                $this->returnCallback(
                    function () use (&$iteratorPos) {
                        switch ($iteratorPos) {
                            case 1:
                                return 'text/plain';
                            case 2:
                                return 'text/html';
                        }
                        return 'other';
                    }
                )
            );

        $bodyPartialMock = $this->getMock(
            'Oro\Bundle\ImapBundle\Mail\Storage\Body',
            array('extractContent', 'getPartContentType'),
            array($this->part)
        );
        $bodyPartialMock->expects($this->any())
            ->method('getPartContentType')
            ->will($this->returnValue($contentTypeHeader));
        $bodyPartialMock->expects($this->any())
            ->method('extractContent')
            ->will(
                $this->returnCallback(
                    function () use (&$iteratorPos) {
                        return new Content(
                            (string)$iteratorPos,
                            'SomeContentType',
                            'SomeContentTransferEncoding',
                            'SomeEncoding'
                        );
                    }
                )
            );

        $this->part->expects($this->any())
            ->method('isMultipart')
            ->will($this->returnValue(true));

        $this->mockIterator($this->part, $iteratorPos, $maxIterationCount);

        // Test to TEXT body
        $result = $bodyPartialMock->getContent(Body::FORMAT_TEXT);
        $this->assertEquals(1, $iteratorPos);
        $this->assertEquals(
            new Content('1', 'SomeContentType', 'SomeContentTransferEncoding', 'SomeEncoding'),
            $result
        );

        // Test to HTML body
        $result = $bodyPartialMock->getContent(Body::FORMAT_HTML);
        $this->assertEquals(2, $iteratorPos);
        $this->assertEquals(
            new Content('2', 'SomeContentType', 'SomeContentTransferEncoding', 'SomeEncoding'),
            $result
        );
    }

    private function mockIterator(\PHPUnit_Framework_MockObject_MockObject $obj, &$iteratorPos, &$maxIterationCount)
    {
        $obj->expects($this->any())
            ->method('current')
            ->will($this->returnValue(null));
        $obj->expects($this->any())
            ->method('next')
            ->will(
                $this->returnCallback(
                    function () use (&$iteratorPos) {
                        $iteratorPos++;
                    }
                )
            );
        $obj->expects($this->any())
            ->method('rewind')
            ->will(
                $this->returnCallback(
                    function () use (&$iteratorPos) {
                        $iteratorPos = 1;
                    }
                )
            );
        $obj->expects($this->any())
            ->method('valid')
            ->will(
                $this->returnCallback(
                    function () use (&$iteratorPos, &$maxIterationCount) {
                        return $iteratorPos < $maxIterationCount;
                    }
                )
            );
        $obj->expects($this->any())
            ->method('key')
            ->will(
                $this->returnCallback(
                    function () use (&$iteratorPos) {
                        return $iteratorPos;
                    }
                )
            );
    }

    private function callProtectedMethod($obj, $methodName, array $args)
    {
        $class = new \ReflectionClass($obj);
        $method = $class->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($obj, $args);
    }

    public static function extractContentProvider()
    {
        return array(
            '7bit' => array(
                '7Bit',
                'SomeContentType',
                'SomeCharset',
                'A value',
                new Content('A value', 'SomeContentType', '7Bit', 'SomeCharset')
            ),
            '8bit' => array(
                '8Bit',
                'SomeContentType',
                'SomeCharset',
                'A value',
                new Content('A value', 'SomeContentType', '8Bit', 'SomeCharset')
            ),
            'binary' => array(
                'Binary',
                'SomeContentType',
                'SomeCharset',
                'A value',
                new Content('A value', 'SomeContentType', 'Binary', 'SomeCharset')
            ),
            'base64' => array(
                'Base64',
                'SomeContentType',
                'SomeCharset',
                base64_encode('A value'),
                new Content('A value', 'SomeContentType', 'Base64', 'SomeCharset')
            ),
            'quoted-printable' => array(
                'Quoted-Printable',
                'SomeContentType',
                'SomeCharset',
                quoted_printable_encode('A value='), // = symbol is added to test the 'quoted printable' decoding
                new Content('A value=', 'SomeContentType', 'Quoted-Printable', 'SomeCharset')
            ),
            'Unknown' => array(
                'Unknown',
                'SomeContentType',
                'SomeCharset',
                'A value',
                new Content('A value', 'SomeContentType', 'Unknown', 'SomeCharset')
            ),
            'no charset' => array(
                '8Bit',
                'SomeContentType',
                null,
                'A value',
                new Content('A value', 'SomeContentType', '8Bit', 'ASCII')
            ),
            'no Content-Type' => array(
                '8Bit',
                null,
                null,
                'A value',
                new Content('A value', 'text/plain', '8Bit', 'ASCII')
            ),
            'no Content-Transfer-Encoding' => array(
                null,
                'SomeContentType',
                'SomeCharset',
                'A value',
                new Content('A value', 'SomeContentType', 'BINARY', 'SomeCharset')
            ),
        );
    }
}
