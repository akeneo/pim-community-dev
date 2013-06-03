<?php
namespace Oro\Bundle\UIBundle\Tests\Unit\Twig\Parser;

use Oro\Bundle\UIBundle\Twig\Parser\PositionTokenParser;

class PositionTokenParserTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Oro\Bundle\UIBundle\Twig\Parser\PositionTokenParser
     */
    private $parser;

    public function setUp()
    {
        $this->parser = new PositionTokenParser(array('test_position' => array()), 'test_class');
    }

    public function testParse()
    {
        $startToken = new \Twig_Token(\Twig_Token::NAME_TYPE, 'position', 12);

        $stream = new \Twig_TokenStream(
            array(
                 new \Twig_Token(\Twig_Token::NAME_TYPE, 'test_position', 12),
                 new \Twig_Token(\Twig_Token::NAME_TYPE, 'with', 12),
                 new \Twig_Token(\Twig_Token::BLOCK_END_TYPE, '', 12),
                 new \Twig_Token(\Twig_Token::EOF_TYPE, '', 12),
            )
        );

        $expressionParser = $this->getMockBuilder('\Twig_ExpressionParser')
            ->disableOriginalConstructor()
            ->getMock();

        $parser = $this->getMockBuilder('\Twig_Parser')
            ->disableOriginalConstructor()
            ->getMock();

        $parser->expects($this->once())
            ->method('getStream')
            ->will($this->returnValue($stream));

        $parser->expects($this->once())
            ->method('getExpressionParser')
            ->will($this->returnValue($expressionParser));

        $expressionParser->expects($this->once())
            ->method('parseExpression')
            ->will($this->returnValue(null));

        $this->parser->setParser($parser);

        $resultNode = $this->parser->parse($startToken);
        $this->assertEquals(12, $resultNode->getLine());
        $this->assertEquals('position', $resultNode->getNodeTag());
    }
}

