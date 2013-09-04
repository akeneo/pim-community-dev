<?php

namespace Oro\Bundle\UIBundle\Tests\Unit\Twig\Template;

use Oro\Bundle\UIBundle\Twig\Template;

class TestJson extends Template
{
    public function getTemplateName()
    {
        return 'json.twig';
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        echo json_encode(array('content' => 'test'));
    }
}
