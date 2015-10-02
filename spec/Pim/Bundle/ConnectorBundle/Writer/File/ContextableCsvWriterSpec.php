<?php

namespace spec\Pim\Bundle\ConnectorBundle\Writer\File;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ContextableCsvWriterSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\ConnectorBundle\Writer\File\ContextableCsvWriter');
    }

    function it_is_contextable()
    {
        $this->setConfiguration(['mainContext' => [
            'locale' => 'fr_FR',
            'scope'  => 'ecommerce',
        ]]);

        $this->getContext()->shouldReturn(['locale' => 'fr_FR', 'scope' => 'ecommerce']);
    }

    function it_builds_path_using_context()
    {
        $this->setFilePath('/tmp/products_%locale%_%scope%.csv');

        $this->setConfiguration(['mainContext' => [
            'locale' => 'fr_FR',
            'scope'  => 'ecommerce',
        ]]);

        $this->getPath()->shouldReturn('/tmp/products_fr_FR_ecommerce.csv');
    }
}
