<?php

namespace spec\Pim\Bundle\CatalogBundle\Updater\Setter;

use PhpSpec\ObjectBehavior;
use Symfony\Component\HttpFoundation\File\File;
use Pim\Bundle\CatalogBundle\Builder\ProductBuilder;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;

class TextValueSetterSpec extends ObjectBehavior
{
    function let(ProductBuilder $builder)
    {
        $this->beConstructedWith($builder);
    }

    function it_is_a_setter()
    {
        $this->shouldImplement('Pim\Bundle\CatalogBundle\Updater\Setter\SetterInterface');
    }

    function it_supports_text_attributes(AttributeInterface $textAttribute, AttributeInterface $textareaAttribute, AttributeInterface $numberAttribute)
    {
        $textAttribute->getAttributeType()->willReturn('pim_catalog_text');
        $this->supports($textAttribute)->shouldReturn(true);

        $textareaAttribute->getAttributeType()->willReturn('pim_catalog_textarea');
        $this->supports($textareaAttribute)->shouldReturn(true);

        $numberAttribute->getAttributeType()->willReturn('pim_catalog_number');
        $this->supports($numberAttribute)->shouldReturn(false);
    }
}
