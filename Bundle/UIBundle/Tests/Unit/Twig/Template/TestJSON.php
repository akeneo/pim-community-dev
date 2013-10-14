<?php

namespace Oro\Bundle\UIBundle\Tests\Unit\Twig\Template;

use Oro\Bundle\UIBundle\Twig\Template;

class TestJSON extends Template
{
    public function getTemplateName()
    {
        return 'data.json.twig';
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        echo json_encode(array('content' => "<p>test</p>\n"));
    }
}
