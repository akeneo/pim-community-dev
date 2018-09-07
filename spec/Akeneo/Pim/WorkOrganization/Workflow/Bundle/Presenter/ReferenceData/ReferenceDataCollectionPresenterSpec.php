<?php

namespace spec\Akeneo\Pim\WorkOrganization\Workflow\Bundle\Presenter\ReferenceData;

use Doctrine\Common\Persistence\ObjectRepository;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\ReferenceDataBundle\Doctrine\ReferenceDataRepositoryResolver;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Pim\Component\ReferenceData\Model\ReferenceDataConfigurationInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Rendering\RendererInterface;

class ReferenceDataCollectionPresenterSpec extends ObjectBehavior
{
    function let(ReferenceDataRepositoryResolver $repositoryResolver)
    {
        $this->beConstructedWith($repositoryResolver);
    }

    function it_is_a_presenter()
    {
        $this->shouldBeAnInstanceOf('Akeneo\Pim\WorkOrganization\Workflow\Bundle\Presenter\PresenterInterface');
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
        $repositoryResolver,
        ObjectRepository $repository,
        ReferenceDataConfigurationInterface $configuration,
        RendererInterface $renderer,
        CustomValuePresenterCollection $value,
        AttributeInterface $attribute,
        CustomValuePresenterCollection $leather,
        CustomValuePresenterCollection $neoprene,
        CustomValuePresenterCollection $kevlar
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

        $renderer->renderDiff(['Leather', '[Neoprene]'], ['Leather', 'Kevlar'])->willReturn('diff between two reference data');
        $this->setRenderer($renderer);

        $value->getData()->willReturn([$leather, $neoprene]);
        $value->getAttribute()->willReturn($attribute);
        $attribute->getCode()->willReturn('fabric');
        $this->present($value, ['data' => ['Leather', 'Neoprene']])->shouldReturn('diff between two reference data');
    }
}

interface CustomValuePresenterCollection extends ValueInterface
{
    public function getReferenceDataName();
    public function getCode();
    public function getData();
}
