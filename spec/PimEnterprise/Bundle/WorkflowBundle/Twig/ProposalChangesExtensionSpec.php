<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\Twig;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Doctrine\Common\Persistence\ObjectRepository;
use Pim\Bundle\CatalogBundle\Model;
use PimEnterprise\Bundle\WorkflowBundle\Presenter\PresenterInterface;

class ProposalChangesExtensionSpec extends ObjectBehavior
{
    function let(ObjectRepository $repository)
    {
        $this->beConstructedWith($repository);
    }

    function it_is_a_twig_extension()
    {
        $this->shouldBeAnInstanceOf('\Twig_Extension');
    }

    function it_has_a_name()
    {
        $this->getName()->shouldReturn('pimee_workflow_proposal_changes_extension');
    }

    function it_presents_proposal_change_attribute_using_the_value_id(
        $repository,
        Model\AbstractAttribute $attribute,
        Model\AbstractProductValue $value
    ) {
        $repository->find(123)->willReturn($value);
        $value->getAttribute()->willReturn($attribute);
        $attribute->__toString()->willReturn('Name');

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

    function it_has_presenters(
        PresenterInterface $presenter
    ) {
        $this->addPresenter($presenter);
        $this->getPresenters()->shouldReturn([$presenter]);
    }

    function it_presents_proposal_using_a_supporting_presenter(
        PresenterInterface $presenter1,
        PresenterInterface $presenter2
    ) {
        $this->addPresenter($presenter1);
        $this->addPresenter($presenter2);

        $presenter1->supportsChange(['changes'])->willReturn(false);
        $presenter2->supportsChange(['changes'])->willReturn(true);
        $presenter2->present(['changes'])->willReturn('<b>changes</b>');

        $this->presentChange(['changes'])->shouldReturn('<b>changes</b>');
    }

    function its_presentChange_method_throws_exception_if_no_presenter_support_the_change()
    {
        $this->shouldThrow(new \LogicException('No presenter supports the provided change'))->duringPresentChange([]);
    }
}
