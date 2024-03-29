<?php

namespace Specification\Akeneo\Platform\Installer\Infrastructure\FixtureLoader;

use Akeneo\Tool\Component\Batch\Model\JobInstance;
use PhpSpec\ObjectBehavior;
use Akeneo\Platform\Installer\Infrastructure\FixtureLoader\FixturePathProvider;

class JobInstancesConfiguratorSpec extends ObjectBehavior
{
    function let(FixturePathProvider $pathProvider)
    {
        $this->beConstructedWith($pathProvider);
    }

    function it_configures_job_instances_with_installer_data($pathProvider, JobInstance $instance)
    {
        $myFilePath = __FILE__;
        $myInstallerPath = dirname($myFilePath);
        $myFileName = str_replace($myInstallerPath, '', $myFilePath);
        $pathProvider->getFixturesPath('minimal')->willReturn($myInstallerPath);
        $instance->getRawParameters()->willReturn(['storage' => [
            'type' => 'local',
            'file_path' => $myFileName
        ]]);
        $instance->setRawParameters(['storage' => [
            'type' => 'local',
            'file_path' => $myInstallerPath . $myFileName
        ]])->shouldBeCalled();

        $this->configureJobInstancesWithInstallerData('minimal', [$instance]);
    }

    function it_configures_job_instances_with_a_single_replacement_path(JobInstance $instance)
    {
        $myFilePath = __FILE__;
        $myInstallerPath = dirname($myFilePath);
        $myFileName = str_replace($myInstallerPath, '', $myFilePath);
        $myReplacementFilePath = $myFilePath;
        $replacementPaths = [
            $myFileName  => [
                $myReplacementFilePath
            ]
        ];
        $instance->getCode()->willReturn('my_original_code');
        $instance->getRawParameters()->willReturn(['storage' => [
            'type' => 'local',
            'file_path' => $myFileName
        ]]);
        $instance->setRawParameters(['storage' => [
            'type' => 'local',
            'file_path' => $myReplacementFilePath
        ]])->shouldBeCalled();
        $instance->setCode('my_original_code0')->shouldBeCalled();

        $configuredInstances = $this->configureJobInstancesWithReplacementPaths([$instance], $replacementPaths);
        $configuredInstances->shouldHaveCount(1);
    }

    function it_configures_job_instances_with_several_replacement_paths(JobInstance $instance)
    {
        $myFilePath = __FILE__;
        $myInstallerPath = dirname($myFilePath);
        $myFileName = str_replace($myInstallerPath, '', $myFilePath);
        $myReplacementFileCommunity = $myFilePath;
        $myReplacementFileEnterprise = $myFilePath;
        $replacementPaths = [
            $myFileName  => [$myReplacementFileCommunity, $myReplacementFileEnterprise]
        ];
        $instance->getCode()->willReturn('my_original_code');
        $instance->getRawParameters()->willReturn(['storage' => [
            'type' => 'local',
            'file_path' => $myFileName
        ]]);
        $instance->setRawParameters(['storage' => [
            'type' => 'local',
            'file_path' => $myReplacementFileCommunity
        ]])->shouldBeCalled();
        $instance->setCode('my_original_code0')->shouldBeCalled();

        $instance->getRawParameters()->willReturn(['storage' => [
            'type' => 'local',
            'file_path' => $myFileName
        ]]);
        $instance->setRawParameters(['storage' => [
            'type' => 'local',
            'file_path' => $myReplacementFileEnterprise
        ]])->shouldBeCalled();
        $instance->setCode('my_original_code1')->shouldBeCalled();

        $configuredInstances = $this->configureJobInstancesWithReplacementPaths([$instance], $replacementPaths);
        $configuredInstances->shouldHaveCount(2);
    }
}
