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
        ObjectRepository $repository,
        RendererInterface $renderer,
        TranslatorInterface $translator,
        PresenterInterface $attributePresenter,
        PresenterInterface $valuePresenter
    ) {
        $this->beConstructedWith($repository, $renderer, $translator);

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
        $repository,
        $attributePresenter,
        $valuePresenter,
        Model\AbstractAttribute $attribute,
        Model\AbstractProductValue $value
    ) {
        $repository->find(123)->willReturn($value);
        $value->getAttribute()->willReturn($attribute);
        $value->getScope()->willReturn('ecommerce');

        $attributePresenter->supports($attribute, ['scope' => 'ecommerce'])->willReturn(true);
        $attributePresenter->present($attribute, ['scope' => 'ecommerce'])->willReturn('Name');

        $this->presentAttribute(['id' => '123'], 'foo')->shouldReturn('Name');
    }

    function it_presents_proposal_change_attribute_using_the_default_value_if_id_is_unavailable()
    {
        $this->presentAttribute([], 'foo')->shouldReturn('foo');
    }

    function it_presents_proposal_change_attribute_using_the_default_value_if_value_is_unavailable(
        $repository
    ) {
        $repository->find(123)->willReturn(null);

        $this->presentAttribute(['id' => '123'], 'foo')->shouldReturn('foo');
    }

    function it_presents_proposal_using_a_supporting_presenter(
        $repository,
        $attributePresenter,
        $valuePresenter,
        Model\AbstractProductValue $value
    ) {
        $repository->find('123')->willReturn($value);

        $attributePresenter->supports($value, ['id' => '123', 'foo' => 'bar'])->willReturn(false);
        $valuePresenter->supports($value, ['id' => '123', 'foo' => 'bar'])->willReturn(true);
        $valuePresenter->present($value, ['id' => '123', 'foo' => 'bar'])->willReturn('<b>changes</b>');

        $this->presentChange(['id' => '123', 'foo' => 'bar'])->shouldReturn('<b>changes</b>');
    }

    function its_presentChange_method_throws_exception_if_no_presenter_support_the_change(
        $repository,
        Model\AbstractProductValue $value
    ) {
        $repository->find('123')->willReturn($value);
        $value->getData()->willReturn('foo');

        $this->shouldThrow(new \LogicException('No presenter supports the provided change with key(s) "id, foo"'))->duringPresentChange(['id' => 123, 'foo' => 'bar']);
    }

    function it_injects_translator_in_translator_aware_presenter(
        $repository,
        $translator,
        $attributePresenter,
        $valuePresenter,
        Model\AbstractProductValue $value,
        TranslatorAwarePresenter $presenter
    ){
        $repository->find('123')->willReturn($value);

        $attributePresenter->supports($value, ['id' => '123', 'foo' => 'bar'])->willReturn(false);
        $valuePresenter->supports($value, ['id' => '123', 'foo' => 'bar'])->willReturn(false);
        $presenter->supports($value, ['id' => '123', 'foo' => 'bar'])->willReturn(true);
        $presenter->present($value, ['id' => '123', 'foo' => 'bar'])->willReturn('<b>changes</b>');

        $presenter->setTranslator($translator)->shouldBeCalled();

        $this->addPresenter($presenter, 0);
        $this->presentChange(['id' => '123', 'foo' => 'bar']);
    }

    function it_injects_renderer_in_renderer_aware_presenter(
        $repository,
        $renderer,
        $attributePresenter,
        $valuePresenter,
        Model\AbstractProductValue $value,
        RendererAwarePresenter $presenter
    ){
        $repository->find('123')->willReturn($value);

        $attributePresenter->supports($value, ['id' => '123', 'foo' => 'bar'])->willReturn(false);
        $valuePresenter->supports($value, ['id' => '123', 'foo' => 'bar'])->willReturn(false);
        $presenter->supports($value, ['id' => '123', 'foo' => 'bar'])->willReturn(true);
        $presenter->present($value, ['id' => '123', 'foo' => 'bar'])->willReturn('<b>changes</b>');

        $presenter->setRenderer($renderer)->shouldBeCalled();

        $this->addPresenter($presenter, 0);
        $this->presentChange(['id' => '123', 'foo' => 'bar']);
    }
}

class TranslatorAwarePresenter implements PresenterInterface
{
    use Presenter\TranslatorAware;

    public function supports($object, array $change) { }

    public function present($data, array $change) { }
}

class RendererAwarePresenter implements PresenterInterface
{
    use Presenter\RendererAware;

    public function supports($object, array $change) { }

    public function present($data, array $change) { }
}
