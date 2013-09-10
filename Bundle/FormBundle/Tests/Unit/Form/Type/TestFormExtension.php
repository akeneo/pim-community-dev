<?php

namespace Oro\Bundle\FormBundle\Tests\Unit\Form\Type;

use Symfony\Component\Form\AbstractExtension;
use Genemu\Bundle\FormBundle\Form\JQuery\Type;

class TestFormExtension extends AbstractExtension
{

    protected function loadTypes()
    {
        return array(
            new Type\Select2Type('hidden'),
        );
    }
}
