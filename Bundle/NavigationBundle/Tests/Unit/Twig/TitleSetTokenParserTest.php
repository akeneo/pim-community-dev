<?php

namespace Oro\Bundle\NavigationBundle\Tests\Unit\Twig;

use Oro\Bundle\NavigationBundle\Twig\TitleSetTokenParser;

class TitleSetTokenParserTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tests token parser
     */
    public function testParsing()
    {
        $node = $this->getMock('Twig_Node');

        $exprParser = $this->getMockBuilder('Twig_ExpressionParser')
                           ->disableOriginalConstructor()
                           ->getMock();
        $exprParser->expects($this->once())
                   ->method('parseArguments')
                   ->will($this->returnValue($node));

        $stream = $this->getMockBuilder('Twig_TokenStream')
            ->disableOriginalConstructor()
            ->getMock();
        $stream->expects($this->once())
            ->method('expect')
            ->with($this->equalTo(\Twig_Token::BLOCK_END_TYPE));

        $parser = $this->getMockBuilder('Twig_Parser')
                       ->disableOriginalConstructor()
                       ->getMock();
        $parser->expects($this->once())
               ->method('getExpressionParser')
               ->will($this->returnValue($exprParser));
        $parser->expects($this->once())
               ->method('getStream')
               ->will($this->returnValue($stream));

        $token = $this->getMockBuilder('Twig_Token')
                      ->disableOriginalConstructor()
                      ->getMock();
        $token->expects($this->once())
              ->method('getLine')
              ->will($this->returnValue(1));

        $tokenParser = new TitleSetTokenParser();
        $tokenParser->setParser($parser);
        $tokenParser->parse($token);
    }

    /**
     * Tests tag name
     */
    public function testTagName()
    {
        $tokenParser = new TitleSetTokenParser();
        $this->assertEquals('oro_title_set', $tokenParser->getTag());
    }
}
