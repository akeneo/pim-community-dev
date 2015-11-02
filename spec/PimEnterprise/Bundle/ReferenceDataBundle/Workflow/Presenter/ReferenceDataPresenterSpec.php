<?php

namespace spec\PimEnterprise\Bundle\ReferenceDataBundle\Workflow\Presenter;

use Doctrine\Common\Persistence\ObjectRepository;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Pim\Bundle\ReferenceDataBundle\Doctrine\ReferenceDataRepositoryResolver;
use Pim\Component\ReferenceData\Model\ConfigurationInterface;
use PimEnterprise\Bundle\WorkflowBundle\Rendering\RendererInterface;

class ReferenceDataPresenterSpec extends ObjectBehavior
{
    function let(ReferenceDataRepositoryResolver $repositoryResolver)
    {
        $this->beConstructedWith($repositoryResolver);
    }

    function it_is_a_presenter()
    {
        $this->shouldBeAnInstanceOf('PimEnterprise\Bundle\WorkflowBundle\Presenter\PresenterInterface');
    }

    function it_supports_a_simple_reference_data()
    {
        $this->supportsChange('pim_reference_data_simpleselect')->shouldBe(true);
    }

    function it_does_not_support_a_multi_reference_data()
    {
        $this->supportsChange('pim_reference_data_multiselect')->shouldBe(false);
    }

    function it_presents_reference_data_change_using_the_injected_renderer(
        $repositoryResolver,
        ObjectRepository $repository,
        ConfigurationInterface $configuration,
        RendererInterface $renderer,
        CustomProductValuePresenter $value,
        CustomProductValuePresenter $red,
        CustomProductValuePresenter $blue
    ) {
        $red->__toString()->willReturn('[Red]');
        $red->getReferenceDataName()->willReturn('color');
        $blue->__toString()->willReturn('Blue');

        $configuration->getClass()->willReturn('Acme\Bundle\AppBundle\Entity\Color');
        $repositoryResolver->resolve(null)->willReturn($repository);
        $repository->findOneBy(['code' => 'red'])->willReturn($blue);

        $renderer->renderOriginalDiff('[Red]', 'Blue')->willReturn('diff between two reference data');
        $this->setRenderer($renderer);

        $value->getData()->willReturn($red);
        $this->presentOriginal($value, ['data' => 'red'])->shouldReturn('diff between two reference data');
    }
}

interface CustomProductValuePresenter extends ProductValueInterface
{
    public function getReferenceDataName();
    public function getCode();
    public function getData();
}
