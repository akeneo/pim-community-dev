<?php

namespace Specification\Akeneo\Pim\WorkOrganization\Workflow\Bundle\Presenter\ReferenceData;

use Acme\Bundle\AppBundle\Entity\Color;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Presenter\PresenterInterface;
use Doctrine\Common\Persistence\ObjectRepository;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Bundle\Doctrine\ReferenceDataRepositoryResolver;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Structure\Component\Model\ReferenceDataConfigurationInterface;

class ReferenceDataPresenterSpec extends ObjectBehavior
{
    function let(
        ReferenceDataRepositoryResolver $repositoryResolver
    ) {
        $this->beConstructedWith($repositoryResolver);
    }

    function it_is_a_presenter()
    {
        $this->shouldBeAnInstanceOf(PresenterInterface::class);
    }

    function it_supports_a_simple_reference_data()
    {
        $this->supports('pim_reference_data_simpleselect')->shouldBe(true);
    }

    function it_does_not_support_a_multi_reference_data()
    {
        $this->supports('pim_reference_data_multiselect')->shouldBe(false);
    }

    function it_presents_reference_data_change_using_the_injected_renderer(
        $repositoryResolver,
        ObjectRepository $repository,
        ReferenceDataConfigurationInterface $configuration,
        CustomValuePresenter $red,
        CustomValuePresenter $blue
    ) {
        $red->__toString()->willReturn('[Red]');
        $red->getReferenceDataName()->willReturn('color');
        $blue->__toString()->willReturn('Blue');

        $configuration->getClass()->willReturn(Color::class);
        $repositoryResolver->resolve('color')->willReturn($repository);
        $repository->findOneBy(['code' => 'red'])->willReturn($blue);

        $this->present($red, ['data' => 'red', 'reference_data_name' => 'color'])->shouldReturn([
            'before' => $red,
            'after' => 'Blue',
        ]);
    }
}

interface CustomValuePresenter extends ValueInterface
{
    public function getReferenceDataName();
    public function getCode();
    public function getData();
}
