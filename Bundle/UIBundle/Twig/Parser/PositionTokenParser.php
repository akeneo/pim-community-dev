<?php
namespace Oro\Bundle\UIBundle\Twig\Parser;

use Oro\Bundle\UIBundle\Twig\Node\PositionNode;

class PositionTokenParser extends \Twig_TokenParser
{
    /**
     * @var array
     */
    protected $positions;

    /**
     * @param array $positions Array with positions
     */
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

        if (isset($this->positions[$name])) {
            return new PositionNode($this->positions[$name], $token->getLine(), $this->getTag());
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