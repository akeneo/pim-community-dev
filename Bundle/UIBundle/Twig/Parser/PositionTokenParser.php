<?php
namespace Oro\Bundle\UIBundle\Twig\Parser;

use Oro\Bundle\UIBundle\Twig\Node\PositionNode;

class PositionTokenParser extends \Twig_TokenParser
{
    protected $positions;

    public function __construct(array $positions)
    {
        $this->positions = $positions;
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

        return new PositionNode($this->positions[$name], $token->getLine(), $this->getTag());
    }

    /**
     * {@inheritDoc}
     */
    public function getTag()
    {
        return 'position';
    }

}