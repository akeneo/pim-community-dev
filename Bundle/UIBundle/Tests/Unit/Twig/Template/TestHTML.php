<?php

namespace Oro\Bundle\UIBundle\Tests\Unit\Twig\Template;

use Oro\Bundle\UIBundle\Twig\Template;

class TestHTML extends Template
{
    public function getTemplateName()
    {
        return 'block.html.twig';
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        echo "<p>test string</p>\n";
    }
}
