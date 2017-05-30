<?php

namespace spec\Pim\Bundle\InstallerBundle\FixtureLoader;

use PhpSpec\ObjectBehavior;

class FixturePathProviderSpec extends ObjectBehavior
{
    function it_provides_a_full_path_from_short_definition()
    {
        $bundles = [
            'PimDashboardBundle' => 'Pim\Bundle\DashboardBundle\PimDashboardBundle',
            'PimInstallerBundle' => 'Pim\Bundle\InstallerBundle\PimInstallerBundle'
        ];
        $installerData = 'PimInstallerBundle:minimal';
        $this->beConstructedWith($bundles, $installerData);
        $reflection = new \ReflectionClass('Pim\Bundle\InstallerBundle\PimInstallerBundle');
        $expected = $installerDataDir = dirname($reflection->getFilename()) . '/Resources/fixtures/minimal/';
        $this->getFixturesPath()->shouldReturn($expected);
    }

    function it_provides_a_full_path_when_no_short_definition_is_provided()
    {
        $bundles = [
            'PimDashboardBundle' => 'Pim\Bundle\DashboardBundle\PimDashboardBundle',
            'PimInstallerBundle' => 'Pim\Bundle\InstallerBundle\PimInstallerBundle'
        ];
        $reflection = new \ReflectionClass('Pim\Bundle\InstallerBundle\PimInstallerBundle');
        $installerData = dirname($reflection->getFileName());
        $this->beConstructedWith($bundles, $installerData);
        $this->getFixturesPath()->shouldReturn($installerData.DIRECTORY_SEPARATOR);
    }

    function it_throws_an_exception_when_the_directory_does_not_exist()
    {
        $bundles = [
            'PimDashboardBundle' => 'Pim\Bundle\DashboardBundle\PimDashboardBundle',
            'PimInstallerBundle' => 'Pim\Bundle\InstallerBundle\PimYoloBundle'
        ];
        $installerData = '/tmp/FakeProject/YoloBundle';
        $this->beConstructedWith($bundles, $installerData);
        $this->shouldThrow('\RuntimeException')->during('getFixturesPath', []);
    }
}
