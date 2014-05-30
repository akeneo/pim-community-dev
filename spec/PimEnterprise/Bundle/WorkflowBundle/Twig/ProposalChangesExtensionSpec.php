<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\Twig;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Doctrine\Common\Persistence\ObjectRepository;
use Pim\Bundle\CatalogBundle\Model;
use PimEnterprise\Bundle\WorkflowBundle\Presenter\PresenterInterface;
use Symfony\Component\Translation\TranslatorInterface;

class ProposalChangesExtensionSpec extends ObjectBehavior
{
    function let(
        ObjectRepository $repository,
        TranslatorInterface $translator,
        PresenterInterface $attributePresenter,
        PresenterInterface $valuePresenter
    ) {
        $this->beConstructedWith($repository, $translator);

        $this->addPresenter($attributePresenter);
        $this->addPresenter($valuePresenter);
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
        $this->getPresenters()->shouldReturn([$attributePresenter, $valuePresenter]);
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
        PresenterInterface $attributePresenter,
        PresenterInterface $valuePresenter,
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
}
