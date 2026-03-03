<?php

namespace Specification\Akeneo\Platform\Bundle\UIBundle\Controller;

use Akeneo\Tool\Component\StorageUtils\Repository\SearchableRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Bundle\Doctrine\ORM\Repository\AttributeOptionRepository;
use Akeneo\Pim\Enrichment\Component\Product\Repository\GroupRepositoryInterface;
use Akeneo\Pim\Structure\Component\ReferenceData\ConfigurationRegistryInterface;
use Akeneo\Pim\Structure\Component\Model\ReferenceDataConfigurationInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ReferenceDataRepositoryInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;

class AjaxOptionControllerSpec extends ObjectBehavior
{
    public function let(ManagerRegistry $doctrine, ConfigurationRegistryInterface $registry)
    {
        $this->beConstructedWith($doctrine, $registry);
    }

    public function it_returns_options_with_option_repository(
        $doctrine,
        Request $request,
        ParameterBag $query,
        AttributeOptionRepository $repository
    ): void {
        $request->get('search')->willReturn('hello');
        $request->get('referenceDataName')->willReturn(null);
        $request->get('class')->willReturn('Foo\Bar');

        $doctrine->getRepository('Foo\Bar')->willReturn($repository);

        $request->get('dataLocale')->willReturn('fr_FR');
        $request->get('collectionId')->willReturn(42);
        $request->get('options', [])->willReturn([]);

        $repository->getOptions('fr_FR', 42, 'hello', [])->shouldBeCalled();
        $request->get('isCreatable')->willReturn(false);

        $this->listAction($request);
    }

    public function it_returns_options_with_reference_data_repository(
        $doctrine,
        $registry,
        ReferenceDataConfigurationInterface $configuration,
        Request $request,
        ParameterBag $query,
        ReferenceDataRepositoryInterface $repository
    ): void {
        $request->get('search')->willReturn('hello');
        $request->get('referenceDataName')->willReturn('color');
        $request->get('class')->willReturn('undefined');

        $registry->get('color')->willReturn($configuration);
        $configuration->getClass()->willReturn('Foo\RefData');

        $doctrine->getRepository('Foo\RefData')->willReturn($repository);
        $request->get('options', [])->willReturn([]);
        $repository->findBySearch('hello', [])->shouldBeCalled();
        $request->get('isCreatable')->willReturn(false);

        $this->listAction($request);
    }

    public function it_returns_options_with_searchable_repository(
        $doctrine,
        Request $request,
        ParameterBag $query,
        SearchableRepositoryInterface $repository
    ): void {
        $request->get('search')->willReturn('hello');
        $request->get('referenceDataName')->willReturn(null);
        $request->get('class')->willReturn('Foo\Bar');

        $doctrine->getRepository('Foo\Bar')->willReturn($repository);

        $request->get('options', [])->willReturn([]);

        $repository->findBySearch('hello', [])->shouldBeCalled();
        $request->get('isCreatable')->willReturn(false);

        $this->listAction($request);
    }

    public function it_returns_options_with_other_repository(
        $doctrine,
        Request $request,
        ParameterBag $query,
        GroupRepositoryInterface $repository
    ): void {
        $request->get('search')->willReturn('hello');
        $request->get('referenceDataName')->willReturn(null);
        $request->get('class')->willReturn('Foo\Bar');

        $doctrine->getRepository('Foo\Bar')->willReturn($repository);

        $request->get('dataLocale')->willReturn('fr_FR');
        $request->get('collectionId')->willReturn(42);
        $request->get('options', [])->willReturn([]);

        $repository->getOptions('fr_FR', 42, 'hello', [])->shouldBeCalled();
        $request->get('isCreatable')->willReturn(false);

        $this->listAction($request);
    }

    public function it_throws_an_exception_if_no_repository_can_be_found(
        $doctrine,
        Request $request,
        \stdClass $repository
    ): void {
        $request->get('search')->willReturn('hello');
        $request->get('referenceDataName')->willReturn(null);
        $request->get('class')->willReturn('Foo\Bar');

        $doctrine->getRepository('Foo\Bar')->willReturn($repository);

        $this->shouldThrow(
            new \LogicException('The repository of the class "Foo\Bar" can not retrieve options via Ajax.')
        )->during('listAction', [$request]);
    }
}
