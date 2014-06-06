<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\Twig;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Doctrine\Common\Persistence\ObjectRepository;
use Pim\Bundle\CatalogBundle\Model;
use PimEnterprise\Bundle\WorkflowBundle\Presenter\PresenterInterface;
use Symfony\Component\Translation\TranslatorInterface;
use PimEnterprise\Bundle\WorkflowBundle\Rendering\RendererInterface;
use PimEnterprise\Bundle\WorkflowBundle\Presenter;

class ProposalChangesExtensionSpec extends ObjectBehavior
{
    function let(
        ObjectRepository $valueRepository,
        ObjectRepository $attributeRepository,
        RendererInterface $renderer,
        TranslatorInterface $translator,
        PresenterInterface $attributePresenter,
        PresenterInterface $valuePresenter
    ) {
        $this->beConstructedWith($valueRepository, $attributeRepository, $renderer, $translator);

        $this->addPresenter($attributePresenter, 0);
        $this->addPresenter($valuePresenter, 1);
    }

    function it_is_a_twig_extension()
    {
        $this->shouldBeAnInstanceOf('\Twig_Extension');
    }

    function it_has_a_name()
    {
        $this->getName()->shouldReturn('pimee_workflow_proposal_changes_extension');
    }

    function it_has_presenters(
        $attributePresenter,
        $valuePresenter
    ) {
        $this->getPresenters()->shouldReturn([$valuePresenter, $attributePresenter]);
    }

    function it_presents_proposal_change_attribute_using_a_supporting_presenter(
        $valueRepository,
        $attributeRepository,
        $attributePresenter,
        $valuePresenter,
        Model\AbstractAttribute $attribute,
        Model\AbstractProductValue $value
    ) {
        $attributeRepository->find(123)->willReturn($attribute);

        $attributePresenter->supports($attribute, ['__context__' => ['attribute_id' => '123']])->willReturn(true);
        $attributePresenter->present($attribute, ['__context__' => ['attribute_id' => '123']])->willReturn('Name');

        $this->presentAttribute(['__context__' => ['attribute_id' => '123']], 'foo')->shouldReturn('Name');
    }

    function it_presents_proposal_change_attribute_using_the_default_value_if_id_is_unavailable()
    {
        $this->presentAttribute([], 'foo')->shouldReturn('foo');
    }

    function it_presents_proposal_change_attribute_using_the_default_value_if_value_is_unavailable(
        $valueRepository
    ) {
        $valueRepository->find(123)->willReturn(null);

        $this->presentAttribute(['__context__' => ['attribute_id' => '123']], 'foo')->shouldReturn('foo');
    }

    function it_presents_proposal_using_a_supporting_presenter(
        $valueRepository,
        $attributePresenter,
        $valuePresenter,
        Model\AbstractProductValue $value
    ) {
        $valueRepository->find('123')->willReturn($value);

        $attributePresenter->supports($value, ['foo' => 'bar', '__context__' => ['value_id' => '123']])->willReturn(false);
        $valuePresenter->supports($value, ['foo' => 'bar', '__context__' => ['value_id' => '123']])->willReturn(true);
        $valuePresenter->present($value, ['foo' => 'bar', '__context__' => ['value_id' => '123']])->willReturn('<b>changes</b>');

        $this->presentChange(['foo' => 'bar', '__context__' => ['value_id' => '123']])->shouldReturn('<b>changes</b>');
    }

    function it_injects_translator_in_translator_aware_presenter(
        $valueRepository,
        $translator,
        $attributePresenter,
        $valuePresenter,
        Model\AbstractProductValue $value,
        PresenterInterface $presenter
    ){
        $presenter->implement('PimEnterprise\Bundle\WorkflowBundle\Presenter\TranslatorAwareInterface');
        $valueRepository->find('123')->willReturn($value);

        $attributePresenter->supports($value, ['foo' => 'bar', '__context__' => ['value_id' => '123']])->willReturn(false);
        $valuePresenter->supports($value, ['foo' => 'bar', '__context__' => ['value_id' => '123']])->willReturn(false);
        $presenter->supports($value, ['foo' => 'bar', '__context__' => ['value_id' => '123']])->willReturn(true);
        $presenter->present($value, ['foo' => 'bar', '__context__' => ['value_id' => '123']])->willReturn('<b>changes</b>');

        $presenter->setTranslator($translator)->shouldBeCalled();

        $this->addPresenter($presenter, 0);
        $this->presentChange(['foo' => 'bar', '__context__' => ['value_id' => '123']]);
    }

    function it_injects_renderer_in_renderer_aware_presenter(
        $valueRepository,
        $renderer,
        $attributePresenter,
        $valuePresenter,
        Model\AbstractProductValue $value,
        PresenterInterface $presenter
    ){
        $presenter->implement('PimEnterprise\Bundle\WorkflowBundle\Presenter\RendererAwareInterface');
        $valueRepository->find('123')->willReturn($value);

        $attributePresenter->supports($value, ['foo' => 'bar', '__context__' => ['value_id' => '123']])->willReturn(false);
        $valuePresenter->supports($value, ['foo' => 'bar', '__context__' => ['value_id' => '123']])->willReturn(false);
        $presenter->supports($value, ['foo' => 'bar', '__context__' => ['value_id' => '123']])->willReturn(true);
        $presenter->present($value, ['foo' => 'bar', '__context__' => ['value_id' => '123']])->willReturn('<b>changes</b>');

        $presenter->setRenderer($renderer)->shouldBeCalled();

        $this->addPresenter($presenter, 0);
        $this->presentChange(['foo' => 'bar', '__context__' => ['value_id' => '123']]);
    }
}
