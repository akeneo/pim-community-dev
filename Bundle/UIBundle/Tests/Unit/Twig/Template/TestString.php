<?php

namespace Oro\Bundle\UIBundle\Tests\Unit\Twig\Template;

use Oro\Bundle\UIBundle\Twig\Template;

class TestString extends Template
{
    public function getTemplateName()
    {
        return 'string.twig';
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        echo 'test string';
    }
}
