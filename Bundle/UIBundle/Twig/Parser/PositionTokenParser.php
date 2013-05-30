<?php
namespace Oro\Bundle\UIBundle\Twig\Parser;

use Oro\Bundle\UIBundle\Twig\Node\PositionNode;

class PositionTokenParser extends \Twig_TokenParser
{
    /**
     * @var array
     */
    protected $positions;

    protected $wrapClassName;

    /**
     * @param array  $positions Array with positions
     * @param string $wrapClassName Wrapper css class
     */
    public function __construct(array $positions, $wrapClassName)
    {
        $this->positions = $positions;
        $this->wrapClassName = $wrapClassName;
    }
    /**
     * {@inheritDoc}
     */
    public function parse(\Twig_Token $token)
    {
        $parser = $this->parser;
        $stream = $parser->getStream();

        $name = $stream->expect(\Twig_Token::NAME_TYPE)->getValue();
        $stream->expect(\Twig_Token::BLOCK_END_TYPE);

        if (isset($this->positions[$name])) {
            return new PositionNode($this->positions[$name], $this->wrapClassName, $token->getLine(), $this->getTag());
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getTag()
    {
        return 'position';
    }

}