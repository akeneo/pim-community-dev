<?php

namespace Oro\Bundle\UIBundle\Twig;

use Oro\Bundle\UIBundle\Twig\Parser\PlaceholderTokenParser;

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
        return array(
            new PlaceholderTokenParser($this->placeholders, $this->wrapClassName)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'oro_ui';
    }
}
