<?php

namespace Specification\Akeneo\Platform\Bundle\InstallerBundle\FixtureLoader;

use Akeneo\Platform\Bundle\DashboardBundle\PimDashboardBundle;
use Akeneo\Platform\Bundle\InstallerBundle\PimInstallerBundle;
use PhpSpec\ObjectBehavior;

class FixturePathProviderSpec extends ObjectBehavior
{
    function it_provides_a_full_path_from_short_definition()
    {
        $bundles = [
            'PimDashboardBundle' => PimDashboardBundle::class,
            'PimInstallerBundle' => PimInstallerBundle::class
        ];
        $this->beConstructedWith($bundles);
        $reflection = new \ReflectionClass(PimInstallerBundle::class);
        $expected = dirname($reflection->getFilename()) . '/Resources/fixtures/minimal/';
        $this->getFixturesPath('PimInstallerBundle:minimal')->shouldReturn($expected);
    }

    function it_provides_a_full_path_when_no_short_definition_is_provided()
    {
        $bundles = [
            'PimDashboardBundle' => PimDashboardBundle::class,
            'PimInstallerBundle' => PimInstallerBundle::class
        ];
        $reflection = new \ReflectionClass(PimInstallerBundle::class);
        $installerData = dirname($reflection->getFileName());
        $this->beConstructedWith($bundles);
        $this->getFixturesPath($installerData)->shouldReturn($installerData.DIRECTORY_SEPARATOR);
    }

    function it_throws_an_exception_when_the_directory_does_not_exist()
    {
        $bundles = [
            'PimDashboardBundle' => PimDashboardBundle::class,
            'PimInstallerBundle' => 'Akeneo\Platform\Bundle\InstallerBundle\PimYoloBundle'
        ];
        $this->beConstructedWith($bundles);
        $this->shouldThrow('\RuntimeException')->during('getFixturesPath', ['/tmp/FakeProject/YoloBundle']);
    }
}
