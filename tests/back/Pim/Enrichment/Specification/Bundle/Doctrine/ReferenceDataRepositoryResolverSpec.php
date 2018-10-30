<?php

namespace Specification\Akeneo\Pim\Enrichment\Bundle\Doctrine;

use Acme\Bundle\AppBundle\Entity\Color;
use Akeneo\Pim\Enrichment\Bundle\Doctrine\ReferenceDataRepositoryResolver;
use Doctrine\Common\Persistence\ObjectRepository;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\ReferenceData\ConfigurationRegistryInterface;
use Akeneo\Pim\Structure\Component\Model\ReferenceDataConfigurationInterface;
use Symfony\Bridge\Doctrine\RegistryInterface;

class ReferenceDataRepositoryResolverSpec extends ObjectBehavior
{
    function let(ConfigurationRegistryInterface $configurationRegistry, RegistryInterface $doctrine)
    {
        $this->beConstructedWith($configurationRegistry, $doctrine);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ReferenceDataRepositoryResolver::class);
    }

    function it_resolves_the_repository_of_a_reference_data(
        $configurationRegistry,
        $doctrine,
        ReferenceDataConfigurationInterface $configuration,
        ObjectRepository $repository
    ) {
        $configuration->getClass()->willReturn(Color::class);
        $configurationRegistry->get('colors')->willReturn($configuration);
        $doctrine->getRepository(Color::class)->willReturn($repository);

        $this->resolve('colors')->shouldReturn($repository);
    }
}
