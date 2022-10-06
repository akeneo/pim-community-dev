<?php

namespace Akeneo\Platform\Bundle\UIBundle\Twig;

use Twig\Extension\AbstractExtension;
use Akeneo\Platform\Bundle\UIBundle\Twig\Parser\PlaceholderTokenParser;

class UiExtension extends AbstractExtension
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
