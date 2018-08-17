<?php

namespace Akeneo\Platform\Bundle\UIBundle\Twig;

use Akeneo\Platform\Bundle\UIBundle\Twig\Parser\PlaceholderTokenParser;

class UiExtension extends \Twig_Extension
{
    protected $placeholders;

    protected $wrapClassName;

    public function __construct($placeholders, $wrapClassName)
    {
        $this->placeholders = $placeholders;
        $this->wrapClassName = $wrapClassName;
    }

    /**
     * {@inheritDoc}
     */
    public function getTokenParsers()
    {
        return [
            new PlaceholderTokenParser($this->placeholders, $this->wrapClassName)
        ];
    }
}
