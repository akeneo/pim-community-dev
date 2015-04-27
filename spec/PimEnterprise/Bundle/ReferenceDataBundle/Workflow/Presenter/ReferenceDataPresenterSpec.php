<?php

namespace spec\PimEnterprise\Bundle\ReferenceDataBundle\Workflow\Presenter;

use Doctrine\Common\Persistence\ObjectRepository;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Entity\Attribute;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Pim\Bundle\CatalogBundle\Repository\AttributeRepositoryInterface;
use Pim\Bundle\ReferenceDataBundle\Doctrine\ReferenceDataRepositoryResolver;
use Pim\Component\ReferenceData\Model\ConfigurationInterface;
use PimEnterprise\Bundle\WorkflowBundle\Rendering\RendererInterface;

class ReferenceDataPresenterSpec extends ObjectBehavior
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

    function it_supports_a_simple_reference_data($attributeRepository)
    {
        $code = 'color';
        $attribute = new Attribute();
        $attribute->setAttributeType('pim_reference_data_simpleselect');
        $attributeRepository->findOneBy(['code' => $code])->willReturn($attribute);

        $change = ['__context__' => ['attribute' => $code]];
        $this->supportsChange($change)->shouldBe(true);
    }

    function it_does_not_support_a_non_simple_reference_data($attributeRepository)
    {
        $code = 'color';
        $attribute = new Attribute();
        $attribute->setAttributeType('pim_reference_data_multiselect');
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
        CustomProductValuePresenter $value,
        CustomProductValuePresenter $red,
        CustomProductValuePresenter $blue
    ) {
        $red->__toString()->willReturn('[Red]');
        $red->getReferenceDataName()->willReturn('color');
        $blue->__toString()->willReturn('Blue');

        $configuration->getClass()->willReturn('Acme\Bundle\AppBundle\Entity\Color');
        $repositoryResolver->resolve('color')->willReturn($repository);
        $repository->find(1)->willReturn($blue);
        $attributeRepository->findOneBy(['code' => 'red'])->willReturn($red);

        $renderer->renderDiff('[Red]', 'Blue')->willReturn('diff between two reference data');
        $this->setRenderer($renderer);

        $value->getData()->willReturn($red);
        $this->present($value, ['__context__' => ['attribute' => 'red'], 'color' => 1])->shouldReturn('diff between two reference data');
    }
}

interface CustomProductValuePresenter extends ProductValueInterface
{
    public function getReferenceDataName();
    public function getCode();
    public function getData();
}
