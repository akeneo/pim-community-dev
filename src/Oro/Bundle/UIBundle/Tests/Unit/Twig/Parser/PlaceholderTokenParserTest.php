<?php
namespace Oro\Bundle\UIBundle\Tests\Unit\Twig\Parser;

use Oro\Bundle\UIBundle\Twig\Parser\PlaceholderTokenParser;

class PlaceholderTokenParserTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Oro\Bundle\UIBundle\Twig\Parser\PlaceholderTokenParser
     */
    private $placeholder;

    public function setUp()
    {
        $this->placeholder = new PlaceholderTokenParser(
            array(
                 'test_position' => array(
                     'items' => array(
                         'test_item' => array()
                     )
                 )
            ),
            'test_class'
        );
    }

    public function testParse()
    {
        $startToken = new \Twig_Token(\Twig_Token::NAME_TYPE, 'placeholder', 12);

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

        $this->placeholder->setParser($parser);

        $resultNode = $this->placeholder->parse($startToken);

        $this->assertEquals(12, $resultNode->getLine());
        $this->assertEquals('placeholder', $resultNode->getNodeTag());
    }
}
