<?php

namespace Oro\Bundle\ImapBundle\Tests\Unit\Mail\Storage;

use Oro\Bundle\ImapBundle\Mail\Storage\Attachment;
use Oro\Bundle\ImapBundle\Mail\Storage\Content;
use Oro\Bundle\ImapBundle\Mail\Storage\Value;

class AttachmentTest extends \PHPUnit_Framework_TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $part;

    /** @var Attachment */
    private $attachment;

    protected function setUp()
    {
        $this->part = $this->getMockBuilder('Zend\Mail\Storage\Part')
            ->disableOriginalConstructor()
            ->getMock();

        $this->attachment = new Attachment($this->part);
    }

    public function testGetHeaders()
    {
        $headers = new \stdClass();

        $this->part
            ->expects($this->once())
            ->method('getHeaders')
            ->will($this->returnValue($headers));

        $result = $this->attachment->getHeaders();

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

        $result = $this->attachment->getHeader('SomeHeader', 'string');

        $this->assertTrue($header === $result);
    }

    public function testGetFileNameWithContentDispositionExists()
    {
        $testFileName = 'SomeFile';
        $testEncoding = 'SomeEncoding';

        // Content-Disposition header
        $contentDispositionHeader = $this->getMockBuilder('Zend\Mail\Header\GenericHeader')
            ->disableOriginalConstructor()
            ->getMock();
        $contentDispositionHeader->expects($this->once())
            ->method('getFieldValue')
            ->will($this->returnValue('attachment; filename=' . $testFileName));
        $contentDispositionHeader->expects($this->once())
            ->method('getEncoding')
            ->will($this->returnValue($testEncoding));

        // Headers object
        $headers = $this->getMockBuilder('Zend\Mail\Headers')
            ->disableOriginalConstructor()
            ->getMock();
        $headers->expects($this->once())
            ->method('has')
            ->with($this->equalTo('Content-Disposition'))
            ->will($this->returnValue(true));

        // Part object
        $this->part->expects($this->once())
            ->method('getHeaders')
            ->will($this->returnValue($headers));
        $this->part
            ->expects($this->once())
            ->method('getHeader')
            ->with($this->equalTo('Content-Disposition'))
            ->will($this->returnValue($contentDispositionHeader));

        $result = $this->attachment->getFileName();

        $expected = new Value($testFileName, $testEncoding);
        $this->assertEquals($expected, $result);
    }

    public function testGetFileNameWithContentDispositionDoesNotExist()
    {
        $testFileName = 'SomeFile';
        $testEncoding = 'SomeEncoding';

        // Content-Disposition header
        $contentTypeHeader = $this->getMockBuilder('Zend\Mail\Header\ContentType')
            ->disableOriginalConstructor()
            ->getMock();
        $contentTypeHeader->expects($this->once())
            ->method('getParameter')
            ->with($this->equalTo('name'))
            ->will($this->returnValue($testFileName));
        $contentTypeHeader->expects($this->once())
            ->method('getEncoding')
            ->will($this->returnValue($testEncoding));

        // Headers object
        $headers = $this->getMockBuilder('Zend\Mail\Headers')
            ->disableOriginalConstructor()
            ->getMock();
        $headers->expects($this->any())
            ->method('has')
            ->will(
                $this->returnValueMap(
                    array(
                        array('Content-Disposition', false),
                        array('Content-Type', true)
                    )
                )
            );

        // Part object
        $this->part->expects($this->once())
            ->method('getHeaders')
            ->will($this->returnValue($headers));
        $this->part
            ->expects($this->once())
            ->method('getHeader')
            ->with($this->equalTo('Content-Type'))
            ->will($this->returnValue($contentTypeHeader));

        $result = $this->attachment->getFileName();

        $expected = new Value($testFileName, $testEncoding);
        $this->assertEquals($expected, $result);
    }

    /**
     * @dataProvider getContentProvider
     */
    public function testGetContent(
        $contentTransferEncoding,
        $contentType,
        $contentCharset,
        $contentValue,
        $expected,
        $decodedValue
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

        $result = $this->attachment->getContent();

        $this->assertEquals($expected, $result);
        $this->assertEquals($decodedValue, $result->getDecodedContent());
    }

    public static function getContentProvider()
    {
        return array(
            '7bit' => array(
                '7Bit',
                'SomeContentType',
                'ISO-8859-1',
                'A value',
                new Content('A value', 'SomeContentType', '7Bit', 'ISO-8859-1'),
                'A value'
            ),
            '8bit' => array(
                '8Bit',
                'SomeContentType',
                'ISO-8859-1',
                'A value',
                new Content('A value', 'SomeContentType', '8Bit', 'ISO-8859-1'),
                'A value'
            ),
            'binary' => array(
                'Binary',
                'SomeContentType',
                'ISO-8859-1',
                'A value',
                new Content('A value', 'SomeContentType', 'Binary', 'ISO-8859-1'),
                'A value'
            ),
            'base64' => array(
                'Base64',
                'SomeContentType',
                'ISO-8859-1',
                base64_encode('A value'),
                new Content(base64_encode('A value'), 'SomeContentType', 'Base64', 'ISO-8859-1'),
                'A value'
            ),
            'quoted-printable' => array(
                'Quoted-Printable',
                'SomeContentType',
                'ISO-8859-1',
                quoted_printable_encode('A value='), // = symbol is added to test the 'quoted printable' decoding
                new Content(quoted_printable_encode('A value='), 'SomeContentType', 'Quoted-Printable', 'ISO-8859-1'),
                'A value='
            ),
            'Unknown' => array(
                'Unknown',
                'SomeContentType',
                'ISO-8859-1',
                'A value',
                new Content('A value', 'SomeContentType', 'Unknown', 'ISO-8859-1'),
                'A value'
            ),
            'no charset' => array(
                '8Bit',
                'SomeContentType',
                null,
                'A value',
                new Content('A value', 'SomeContentType', '8Bit', 'ASCII'),
                'A value'
            ),
            'no Content-Type' => array(
                '8Bit',
                null,
                null,
                'A value',
                new Content('A value', 'text/plain', '8Bit', 'ASCII'),
                'A value'
            ),
            'no Content-Transfer-Encoding' => array(
                null,
                'SomeContentType',
                'ISO-8859-1',
                'A value',
                new Content('A value', 'SomeContentType', 'BINARY', 'ISO-8859-1'),
                'A value'
            ),
        );
    }
}
