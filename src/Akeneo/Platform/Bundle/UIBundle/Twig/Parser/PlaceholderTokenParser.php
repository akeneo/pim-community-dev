<?php
namespace Akeneo\Platform\Bundle\UIBundle\Twig\Parser;

use Akeneo\Platform\Bundle\UIBundle\Twig\Node\PlaceholderNode;

class PlaceholderTokenParser extends \Twig_TokenParser
{
    /**
     * @var array
     */
    protected $placeholders;

    protected $wrapClassName;

    /**
     * @param array  $placeholders Array with placeholders
     * @param string $wrapClassName Wrapper css class
     */
    public function __construct(array $placeholders, $wrapClassName)
    {
        $this->placeholders = $placeholders;
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

        $variables = null;
        if ($stream->test(\Twig_Token::NAME_TYPE, 'with')) {
            $stream->next();
            $variables = $this->parser->getExpressionParser()->parseExpression();
        }

        $stream->expect(\Twig_Token::BLOCK_END_TYPE);

        if (isset($this->placeholders[$name])) {
            return new PlaceholderNode(
                $this->placeholders[$name],
                $variables,
                $this->wrapClassName,
                $token->getLine(),
                $this->getTag()
            );
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getTag()
    {
        return 'placeholder';
    }
}
