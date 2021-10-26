<?php

namespace Specification\Akeneo\Pim\Enrichment\Bundle\Doctrine;

use Acme\Bundle\AppBundle\Entity\Color;
use Akeneo\Pim\Enrichment\Bundle\Doctrine\ReferenceDataRepositoryResolver;
use Akeneo\Pim\Structure\Component\ReferenceData\InvalidReferenceDataException;
use Doctrine\Persistence\Mapping\MappingException;
use Doctrine\Persistence\ObjectRepository;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\ReferenceData\ConfigurationRegistryInterface;
use Akeneo\Pim\Structure\Component\Model\ReferenceDataConfigurationInterface;
use Doctrine\Persistence\ManagerRegistry;

class ReferenceDataRepositoryResolverSpec extends ObjectBehavior
{
    function let(ConfigurationRegistryInterface $configurationRegistry, ManagerRegistry $doctrine)
    {
        $this->beConstructedWith($configurationRegistry, $doctrine);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ReferenceDataRepositoryResolver::class);
    }

    function it_resolves_the_repository_of_a_reference_data(
        ConfigurationRegistryInterface $configurationRegistry,
        ManagerRegistry $doctrine,
        ReferenceDataConfigurationInterface $configuration,
        ObjectRepository $repository
    ) {
        $configuration->getClass()->willReturn(Color::class);
        $configurationRegistry->has('colors')->willReturn(true);
        $configurationRegistry->get('colors')->willReturn($configuration);
        $doctrine->getRepository(Color::class)->willReturn($repository);

        $this->resolve('colors')->shouldReturn($repository);
    }

    function it_throws_an_exception_if_the_reference_data_is_not_registered(
        ConfigurationRegistryInterface $configurationRegistry
    ) {
        $configurationRegistry->has('colors')->willReturn(false);

        $this->shouldThrow(InvalidReferenceDataException::class)->during('resolve', ['colors']);
    }

    function it_throws_an_exception_if_the_reference_data_is_not_properly_configured(
        ConfigurationRegistryInterface $configurationRegistry,
        ManagerRegistry $doctrine,
        ReferenceDataConfigurationInterface $configuration
    ) {
        $configuration->getClass()->willReturn(Color::class);
        $configurationRegistry->has('colors')->willReturn(true);
        $configurationRegistry->get('colors')->willReturn($configuration);
        $doctrine->getRepository(Color::class)->willThrow(new MappingException());

        $this->shouldThrow(InvalidReferenceDataException::class)->during('resolve', ['colors']);
    }
}
