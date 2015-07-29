<?php

namespace spec\PimEnterprise\Bundle\ReferenceDataBundle\Workflow\Presenter;

use Doctrine\Common\Persistence\ObjectRepository;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Pim\Bundle\CatalogBundle\Repository\AttributeRepositoryInterface;
use Pim\Bundle\ReferenceDataBundle\Doctrine\ReferenceDataRepositoryResolver;
use Pim\Component\ReferenceData\Model\ConfigurationInterface;
use PimEnterprise\Bundle\WorkflowBundle\Rendering\RendererInterface;

class ReferenceDataCollectionPresenterSpec extends ObjectBehavior
{
    function let(
        AttributeRepositoryInterface $attributeRepository,
        ReferenceDataRepositoryResolver $repositoryResolver
    ) {
        $this->beConstructedWith($attributeRepository, $repositoryResolver);
    }

    function it_is_a_presenter()
    {
        $this->shouldBeAnInstanceOf('PimEnterprise\Bundle\WorkflowBundle\Presenter\PresenterInterface');
    }

    function it_supports_a_multi_reference_data()
    {
        $this->supportsChange('pim_reference_data_multiselect')->shouldBe(true);
    }

    function it_does_not_support_a_simple_reference_data()
    {
        $this->supportsChange('pim_reference_data_simpleselect')->shouldBe(false);
    }

    function it_presents_reference_data_change_using_the_injected_renderer(
        $attributeRepository,
        $repositoryResolver,
        ObjectRepository $repository,
        ConfigurationInterface $configuration,
        RendererInterface $renderer,
        CustomProductValuePresenterCollection $value,
        CustomProductValuePresenterCollection $leather,
        CustomProductValuePresenterCollection $neoprene,
        CustomProductValuePresenterCollection $kevlar
    ) {
        $leather->__toString()->willReturn('Leather');
        $leather->getReferenceDataName()->willReturn('fabrics');
        $neoprene->__toString()->willReturn('[Neoprene]');
        $neoprene->getReferenceDataName()->willReturn('fabrics');
        $kevlar->__toString()->willReturn('Kevlar');
        $kevlar->getReferenceDataName()->willReturn('fabrics');

        $configuration->getClass()->willReturn('Acme\Bundle\AppBundle\Entity\Fabrics');
        $repository->findBy(['code' => ['Leather', 'Neoprene']])->willReturn([$leather, $kevlar]);
        $repositoryResolver->resolve(null)->willReturn($repository);
        $attributeRepository->findOneBy(['code' => 'myfabric'])->willReturn($leather);

        $renderer->renderDiff(['Leather', '[Neoprene]'], ['Leather', 'Kevlar'])->willReturn('diff between two reference data');
        $this->setRenderer($renderer);

        $value->getData()->willReturn([$leather, $neoprene]);
        $this->present($value, ['data' => ['Leather', 'Neoprene']])->shouldReturn('diff between two reference data');
    }
}

interface CustomProductValuePresenterCollection extends ProductValueInterface
{
    public function getReferenceDataName();
    public function getCode();
    public function getData();
}
