<?php

namespace spec\Pim\Bundle\InstallerBundle\FixtureLoader;

use Doctrine\Common\DataFixtures\ReferenceRepository;
use Doctrine\Common\Persistence\ObjectManager;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\InstallerBundle\FixtureLoader\ConfigurationRegistryInterface;
use Pim\Bundle\InstallerBundle\FixtureLoader\LoaderFactory;
use Pim\Bundle\InstallerBundle\FixtureLoader\LoaderInterface;
use Prophecy\Argument;

class MultipleLoaderSpec extends ObjectBehavior
{
    function let(
        ConfigurationRegistryInterface $registry,
        LoaderFactory $factory,
        ObjectManager $manager,
        ReferenceRepository $repository,
        LoaderInterface $loader
    ) {
        $this->beConstructedWith($registry, $factory);
        $factory->create(Argument::cetera())->willReturn($loader);
        $registry->getFixtures(Argument::any())->willReturn(
            [
                [
                    'name'      => 'categories',
                    'extension' => 'csv',
                    'path'      => '/path_to_file/categories.csv'
                ],
                [
                    'name'      => 'attributes',
                    'extension' => 'yml',
                    'path'      => '/path_to_file/attributes.yml'
                ],
                [
                    'name'      => 'channels',
                    'extension' => 'yml',
                    'path'      => '/path_to_file/channels.yml'
                ]
            ]
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\InstallerBundle\FixtureLoader\MultipleLoader');
    }

    function it_loads_files(
        $manager,
        $repository,
        $factory,
        $loader
    ) {
        $loader->load('/path_to_file/categories.csv')->shouldBeCalled();
        $loader->load('/path_to_file/attributes.yml')->shouldBeCalled();
        $loader->load('/path_to_file/channels.yml')->shouldBeCalled();
        $paths = [
            '/path_to_file/channels.yml',
            '/path_to_file/categories.csv',
            '/path_to_file/attributes.yml'
        ];
        $this->load($manager, $repository, $paths);
    }

    function it_throws_a_fixture_loader_exception_if_loading_fixtures_fails(
        $manager,
        $repository,
        $factory,
        $loader
    ) {
        $loader->load(Argument::any())
            ->willThrow('Akeneo\Bundle\BatchBundle\Item\InvalidItemException');
        $paths = ['/path_to_file/categories.csv'];
        $this->shouldThrow('Pim\Bundle\InstallerBundle\Exception\FixtureLoaderException')
            ->during('load', [$manager, $repository, $paths]);
    }
}
