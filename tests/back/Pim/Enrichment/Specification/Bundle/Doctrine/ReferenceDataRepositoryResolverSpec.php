<?php

namespace Specification\Akeneo\Pim\Enrichment\Bundle\Doctrine;

use Acme\Bundle\AppBundle\Entity\Color;
use Akeneo\Pim\Enrichment\Bundle\Doctrine\ReferenceDataRepositoryResolver;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\Persistence\ManagerRegistry;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\ReferenceData\ConfigurationRegistryInterface;
use Akeneo\Pim\Structure\Component\Model\ReferenceDataConfigurationInterface;

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
        $configurationRegistry->get('colors')->willReturn($configuration);
        $doctrine->getRepository(Color::class)->willReturn($repository);

        $this->resolve('colors')->shouldReturn($repository);
    }
}
