<?php

namespace spec\PimEnterprise\Bundle\ReferenceDataBundle\Workflow\Presenter;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\ReferenceDataBundle\Doctrine\ReferenceDataRepositoryResolver;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Pim\Bundle\CatalogBundle\Entity\Attribute;
use Pim\Bundle\CatalogBundle\Repository\AttributeRepositoryInterface;
use Pim\Component\ReferenceData\ConfigurationRegistryInterface;
use PimEnterprise\Bundle\WorkflowBundle\Rendering\RendererInterface;
use Pim\Component\ReferenceData\Model\ConfigurationInterface;
use Doctrine\Common\Persistence\ObjectRepository;

class ReferenceDataCollectionPresenterSpec extends ObjectBehavior
{
    function let(
        AttributeRepositoryInterface $attributeRepository,
        ReferenceDataRepositoryResolver $repositoryResolver
    )
    {
        $this->beConstructedWith($attributeRepository, $repositoryResolver);
    }

    function it_is_a_presenter()
    {
        $this->shouldBeAnInstanceOf('PimEnterprise\Bundle\WorkflowBundle\Presenter\PresenterInterface');
    }

    function it_supports_a_simple_reference_data($attributeRepository)
    {
        $code = 'color';
        $attribute = new Attribute();
        $attribute->setAttributeType('pim_reference_data_multiselect');
        $attributeRepository->findOneBy(['code' => $code])->willReturn($attribute);

        $change = ['__context__' => ['attribute' => $code]];
        $this->supportsChange($change)->shouldBe(true);
    }

    function it_does_not_support_a_non_simple_reference_data($attributeRepository)
    {
        $code = 'color';
        $attribute = new Attribute();
        $attribute->setAttributeType('pim_reference_data_simpleselect');
        $attributeRepository->findOneBy(['code' => $code])->willReturn($attribute);

        $change = ['__context__' => ['attribute' => $code]];
        $this->supportsChange($change)->shouldBe(false);

        $attribute->setAttributeType('other');
        $attributeRepository->findOneBy(['code' => $code])->willReturn($attribute);
        $this->supportsChange($change)->shouldBe(false);
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
        $repository->findBy(['id' => [1, 2]])->willReturn([$leather, $kevlar]);
        $repositoryResolver->resolve('fabrics')->willReturn($repository);
        $attributeRepository->findOneBy(['code' => 'myfabric'])->willReturn($leather);

        $renderer->renderDiff(['Leather', '[Neoprene]'], ['Leather', 'Kevlar'])->willReturn('diff between two reference data');
        $this->setRenderer($renderer);

        $value->getData()->willReturn([$leather, $neoprene]);
        $this->present($value, ['__context__' => ['attribute' => 'myfabric'], 'fabrics' => '1,2'])->shouldReturn('diff between two reference data');
    }
}

interface CustomProductValuePresenterCollection extends ProductValueInterface
{
    public function getReferenceDataName();
    public function getCode();
    public function getData();
}
