<?php

namespace Oro\Bundle\UIBundle\Tests\Unit\Twig\Template;

use Oro\Bundle\UIBundle\Twig\Template;

class TestJS extends Template
{
    public function getTemplateName()
    {
        return 'app.js.twig';
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        echo 'console.log("test")';
    }
}
