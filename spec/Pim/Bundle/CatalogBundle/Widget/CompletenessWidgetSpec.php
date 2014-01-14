<?php

namespace spec\Pim\Bundle\CatalogBundle\Widget;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class CompletenessWidgetSpec extends ObjectBehavior
{
    function it_is_a_widget()
    {
        $this->shouldBeAnInstanceOf('Pim\Bundle\CatalogBundle\Widget\WidgetInterface');
    }

    function it_exposes_the_completeness_widget_template()
    {
        $this->getTemplate()->shouldReturn('PimCatalogBundle:Widget:completeness.html.twig');
    }

    function it_exposes_the_completeness_template_parameters()
    {
        $this->getParameters()->shouldReturn(
            array(
                'Mobile' => array(
                    'total'    => 1000,
                    'complete' => 100,
                    'locales'  => array(
                        'en_US' => array(
                            'total'    => 200,
                            'complete' => 50,
                        ),
                        'fr_FR' => array(
                            'total'    => 800,
                            'complete' => 50,
                        ),
                    ),
                ),
            )
        );
    }
}
