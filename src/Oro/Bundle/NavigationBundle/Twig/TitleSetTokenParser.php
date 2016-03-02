<?php

namespace Oro\Bundle\NavigationBundle\Twig;

/**
 * Class TitleSetTokenParser
 * Used for compiling {% oro_title_set(array) %} tag
 *
 * @package Oro\Bundle\NavigationBundle\Twig
 */
class TitleSetTokenParser extends \Twig_TokenParser
{
    /**
     * Parses a token and returns a node.
     *
     * @param  \Twig_Token         $token A Twig_Token instance
     * @return \Twig_NodeInterface A Twig_NodeInterface instance
     */
    public function parse(\Twig_Token $token)
    {
        $lineno = $token->getLine();

        $expr = $this->parser->getExpressionParser()->parseArguments();
        $this->parser->getStream()->expect(\Twig_Token::BLOCK_END_TYPE);

        return new TitleNode($expr, $lineno);
    }

    /**
     * Gets the tag name associated with this token parser.
     *
     * @return string The tag name
     */
    public function getTag()
    {
        return 'oro_title_set';
    }
}
