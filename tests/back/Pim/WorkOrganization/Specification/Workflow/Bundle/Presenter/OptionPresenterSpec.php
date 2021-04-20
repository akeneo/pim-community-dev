<?php

namespace Specification\Akeneo\Pim\WorkOrganization\Workflow\Bundle\Presenter;

use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Presenter\PresenterInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\Model\AttributeOptionInterface;

class OptionPresenterSpec extends ObjectBehavior
{
    function let(
        IdentifiableObjectRepositoryInterface $optionRepository)
    {
        $this->beConstructedWith($optionRepository);
    }

    function it_is_a_translator_aware_presenter()
    {
        $this->shouldBeAnInstanceOf(PresenterInterface::class);
    }

    function it_supports_simpleselect()
    {
        $this->supports('pim_catalog_simpleselect')->shouldBe(true);
    }

    function it_presents_option_change_using_the_injected_renderer(
        $optionRepository,
        AttributeOptionInterface $blue,
        AttributeOptionInterface $red
    ) {
        $optionRepository->findOneByIdentifier('color.blue')->willReturn($blue);
        $optionRepository->findOneByIdentifier('color.red')->willReturn($red);
        $red->__toString()->willReturn('Red');
        $blue->__toString()->willReturn('Blue');

        $this->present('red', ['data' => 'blue', 'attribute' => 'color'])->shouldReturn([
            'before' => 'Red',
            'after' => 'Blue'
        ]);
    }
}
