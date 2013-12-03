<?php

namespace Oro\Bundle\AsseticBundle\Tests\Unit\Parser;

use \Twig_Token;
use \Twig_TokenStream;

use Oro\Bundle\AsseticBundle\Parser\AsseticTokenParser;

class AsseticTokenParserTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AsseticTokenParser
     */
    private $parser;

    private $assets;

    private $assetsFactory;

    private $tagName;

    public function setUp()
    {
        $this->assetsFactory = $this->getMockBuilder('Symfony\Bundle\AsseticBundle\Factory\AssetFactory')
            ->disableOriginalConstructor()
            ->getMock();

        $this->assets = array(
            'compress' => array(
                array(
                    'first.css',
                    'second.css'
                )
            ),
            'uncompress' => array(
                array(
                    'third.css',
                    'fourth.css'
                )
            )
        );

        $this->tagName = 'oro_css';

        $this->parser = new AsseticTokenParser($this->assets, $this->assetsFactory, $this->tagName, 'css/*.css');
    }

    public function testGetTag()
    {
        $this->assertEquals($this->tagName, $this->parser->getTag());
    }

    public function testTestEndTag()
    {
        $token = new Twig_Token(Twig_Token::NAME_TYPE, 'end' . $this->tagName, 31);
        $this->assertTrue($this->parser->testEndTag($token));
    }

    public function testParse()
    {
        $parser = $this->getMockBuilder('Twig_Parser')
            ->disableOriginalConstructor()
            ->getMock();

        $startToken = new Twig_Token(Twig_Token::NAME_TYPE, 'oro_css', 31);

        $stream = new Twig_TokenStream(
            array(
                new Twig_Token(Twig_Token::NAME_TYPE, 'filter', 31),
                new Twig_Token(Twig_Token::OPERATOR_TYPE, '=', 31),
                new Twig_Token(Twig_Token::STRING_TYPE, 'cssrewrite, lessphp, ?yui_css', 31),
                new Twig_Token(Twig_Token::NAME_TYPE, 'debug', 31),
                new Twig_Token(Twig_Token::OPERATOR_TYPE, '=', 31),
                new Twig_Token(Twig_Token::NAME_TYPE, 'false', 31),
                new Twig_Token(Twig_Token::NAME_TYPE, 'combine', 31),
                new Twig_Token(Twig_Token::OPERATOR_TYPE, '=', 31),
                new Twig_Token(Twig_Token::NAME_TYPE, 'false', 31),
                new Twig_Token(Twig_Token::NAME_TYPE, 'output', 31),
                new Twig_Token(Twig_Token::OPERATOR_TYPE, '=', 31),
                new Twig_Token(Twig_Token::STRING_TYPE, 'css/oro_app.css', 31),
                new Twig_Token(Twig_Token::BLOCK_END_TYPE, '', 31),
                new Twig_Token(Twig_Token::BLOCK_END_TYPE, '', 32),
                new Twig_Token(Twig_Token::BLOCK_START_TYPE, '', 33),
                new Twig_Token(Twig_Token::NAME_TYPE, 'endoro_css', 33),
                new Twig_Token(Twig_Token::BLOCK_END_TYPE, '', 33),
                new Twig_Token(Twig_Token::EOF_TYPE, '', 31),
            )
        );

        $bodyNode = $this->getMockBuilder('\Twig_Node')
            ->disableOriginalConstructor()
            ->getMock();

        $parser->expects($this->once())
            ->method('subparse')
            ->will($this->returnValue($bodyNode));

        $parser->expects($this->once())
            ->method('getStream')
            ->will($this->returnValue($stream));

        $this->parser->setParser($parser);

        $assert = $this->getMockBuilder('Assetic\Asset\AssetCollection')
            ->disableOriginalConstructor()
            ->getMock();

        $this->assetsFactory->expects($this->atLeastOnce())
            ->method('createAsset')
            ->will($this->returnValue($assert));
        /**
         * @var \Symfony\Bundle\AsseticBundle\Twig\AsseticNode
         */
        $resultNode = $this->parser->parse($startToken);

        $this->assertEquals(31, $resultNode->getLine());
        $this->assertEquals('oro_css', $resultNode->getNodeTag());
    }

    public function testParseBrokenStream()
    {
        $parser = $this->getMockBuilder('Twig_Parser')
            ->disableOriginalConstructor()
            ->getMock();

        $brokenStream = new Twig_TokenStream(
            array(
                new Twig_Token(Twig_Token::NAME_TYPE, 'bad', 31),
                new Twig_Token(Twig_Token::OPERATOR_TYPE, '=', 31),
                new Twig_Token(Twig_Token::STRING_TYPE, 'bad value', 31),
            )
        );

        $parser->expects($this->once())
            ->method('getStream')
            ->will($this->returnValue($brokenStream));

        $this->parser->setParser($parser);

        $this->setExpectedException('Twig_Error_Syntax');

        $this->parser->parse(new Twig_Token(Twig_Token::NAME_TYPE, 'oro_css', 31));
    }
}
