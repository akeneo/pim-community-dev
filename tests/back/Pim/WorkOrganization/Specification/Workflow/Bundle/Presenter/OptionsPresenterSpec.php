<?php

namespace Specification\Akeneo\Pim\WorkOrganization\Workflow\Bundle\Presenter;

use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Presenter\PresenterInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\Model\AttributeOptionInterface;

class OptionsPresenterSpec extends ObjectBehavior
{
    function let(
        IdentifiableObjectRepositoryInterface $optionRepository
    ) {
        $this->beConstructedWith($optionRepository);
    }

    function it_is_a_translator_aware_presenter()
    {
        $this->shouldBeAnInstanceOf(PresenterInterface::class);
    }

    function it_supports_multiselect()
    {
        $this->supports('pim_catalog_multiselect')->shouldBe(true);
    }

    function it_presents_options_change_using_the_injected_renderer(
        $optionRepository,
        AttributeOptionInterface $red,
        AttributeOptionInterface $green,
        AttributeOptionInterface $blue
    ) {
        $optionRepository->findOneByIdentifier('color.red')->willReturn($red);
        $optionRepository->findOneByIdentifier('color.green')->willReturn($green);
        $optionRepository->findOneByIdentifier('color.blue')->willReturn($blue);
        $blue->__toString()->willReturn('blue');
        $red->__toString()->willReturn('Red');
        $green->__toString()->willReturn('Green');
        $blue->__toString()->willReturn('Blue');

        $this
            ->present(['red', 'green'], ['data' => ['red', 'green', 'blue'], 'attribute' => 'color'])
            ->shouldReturn([
                'before' => ['Red', 'Green'],
                'after' => ['Red', 'Green', 'Blue'],
            ]);
    }
}
