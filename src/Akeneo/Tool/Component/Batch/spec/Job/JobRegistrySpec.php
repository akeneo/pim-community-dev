<?php

namespace spec\Akeneo\Tool\Component\Batch\Job;

use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlags;
use Akeneo\Tool\Component\Batch\Job\JobInterface;
use Akeneo\Tool\Component\Batch\Job\UndefinedJobException;
use PhpSpec\ObjectBehavior;

class JobRegistrySpec extends ObjectBehavior
{
    function let(
        FeatureFlags $featureFlags,
        JobInterface $referenceEntityJob,
        JobInterface $assetJob,
        JobInterface $productExportJob,
    ) {
        $referenceEntityJob->getName()->willReturn('reference_entity_job');
        $assetJob->getName()->willReturn('asset_manager_job');
        $productExportJob->getName()->willReturn('product_export_job');

        $featureFlags->isEnabled('asset_manager')->willReturn(true);
        $featureFlags->isEnabled('reference_entity')->willReturn(false);

        $this->beConstructedWith($featureFlags);
        $this->register($referenceEntityJob, 'import', 'connector_1', 'reference_entity');
        $this->register($assetJob, 'import', 'connector_2', 'asset_manager');
        $this->register($productExportJob, 'export', 'connector_2');
    }

    function it_gets_a_job_activated_through_feature_flag(JobInterface $assetJob)
    {
        $this->get('asset_manager_job')->shouldReturn($assetJob);
    }

    function it_gets_a_job_even_it_is_disabled_through_feature_flag_to_allow_icecat_job_installation(JobInterface $referenceEntityJob)
    {
        $this->get('reference_entity_job')->shouldReturn($referenceEntityJob);
    }

    function it_return_if_a_job_is_activated_to_make_it_visible_or_not_in_the_process_tracker_for_example(JobInterface $assetJob)
    {
        $this->isEnabled('asset_manager_job')->shouldReturn(true);
    }

    function it_return_if_a_job_is_disabled_to_make_it_invisible_or_not_in_the_process_tracker_for_example(JobInterface $assetJob)
    {
        $this->isEnabled('reference_entity_job')->shouldReturn(false);
    }

    function it_throws_an_exception_when_checking_if_an_non_existent_job_is_activated_or_not(JobInterface $referenceEntityJob)
    {
        $this->shouldThrow(UndefinedJobException::class)->during('isEnabled', ['foo']);
    }

    function it_gets_a_job_when_no_feature_flag_configured_for_it(JobInterface $productExportJob)
    {
        $this->get('product_export_job')->shouldReturn($productExportJob);
    }

    function it_throws_an_exception_when_getting_a_non_existing_job(JobInterface $referenceEntityJob)
    {
        $this->shouldThrow(UndefinedJobException::class)->during('get', ['foo']);
    }

    function it_gets_all_activated_jobs_through_feature_flags(JobInterface $assetJob, JobInterface $productExportJob)
    {
        $this->all()->shouldReturn(['asset_manager_job' => $assetJob, 'product_export_job' => $productExportJob]);
    }

    function it_gets_all_by_type(JobInterface $assetJob, JobInterface $productExportJob)
    {
        $this->allByType('import')->shouldReturn(['asset_manager_job' => $assetJob]);
    }

    function it_gets_all_by_type_group_by_connector(JobInterface $assetJob, JobInterface $productExportJob)
    {
        $this->allByTypeGroupByConnector('import')->shouldReturn(['connector_2' => ['asset_manager_job' => $assetJob]]);
    }

    function it_gets_connectors()
    {
        $this->getConnectors('import')->shouldReturn(['asset_manager_job' => 'connector_2']);
    }

}
