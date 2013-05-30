<?php

namespace Oro\Bundle\UIBundle\Twig;

use Oro\Bundle\UIBundle\Twig\Parser\PositionTokenParser;

class UiExtension extends \Twig_Extension
{
    protected $positions;

    public function __construct($positions)
    {
        $this->positions = $positions;
    }

    /**
     * {@inheritDoc}
     */
    public function getTokenParsers()
    {
        return array(
            new PositionTokenParser($this->positions)
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