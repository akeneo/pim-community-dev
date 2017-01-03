<?php

namespace spec\Pim\Bundle\ReferenceDataBundle\Doctrine;

use Doctrine\Common\Persistence\ObjectRepository;
use PhpSpec\ObjectBehavior;
use Pim\Component\ReferenceData\ConfigurationRegistryInterface;
use Pim\Component\ReferenceData\Model\ConfigurationInterface;
use Symfony\Bridge\Doctrine\RegistryInterface;

class ReferenceDataRepositoryResolverSpec extends ObjectBehavior
{
    function let(ConfigurationRegistryInterface $configurationRegistry, RegistryInterface $doctrine)
    {
        $this->beConstructedWith($configurationRegistry, $doctrine);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\ReferenceDataBundle\Doctrine\ReferenceDataRepositoryResolver');
    }

    function it_resolves_the_repository_of_a_reference_data(
        $configurationRegistry,
        $doctrine,
        ConfigurationInterface $configuration,
        ObjectRepository $repository
    ) {
        $configuration->getClass()->willReturn('Acme\Bundle\AppBundle\Entity\Color');
        $configurationRegistry->get('colors')->willReturn($configuration);
        $doctrine->getRepository('Acme\Bundle\AppBundle\Entity\Color')->willReturn($repository);

        $this->resolve('colors')->shouldReturn($repository);
    }
}
