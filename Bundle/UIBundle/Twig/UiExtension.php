<?php

namespace Oro\Bundle\UIBundle\Twig;

use Oro\Bundle\UIBundle\Twig\Parser\PositionTokenParser;

class UiExtension extends \Twig_Extension
{
    protected $positions;

    protected $wrapClassName;

    public function __construct($positions, $wrapClassName)
    {
        $this->positions = $positions;
        $this->wrapClassName = $wrapClassName;
    }

    /**
     * {@inheritDoc}
     */
    public function getTokenParsers()
    {
        return array(
            new PositionTokenParser($this->positions, $this->wrapClassName)
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