<?php

namespace spec\PimEnterprise\Bundle\ProductAssetBundle\Connector\Reader\MassEdit;

use Akeneo\Component\Batch\Job\JobParameters;
use Akeneo\Component\Batch\Model\StepExecution;
use PimEnterprise\Bundle\ProductAssetBundle\Connector\Reader\MassEdit\FilteredAssetReader;
use PhpSpec\ObjectBehavior;
use PimEnterprise\Component\ProductAsset\Model\AssetInterface;
use PimEnterprise\Component\ProductAsset\Repository\AssetRepositoryInterface;

class FilteredAssetReaderSpec extends ObjectBehavior
{
    function let(AssetRepositoryInterface $repository)
    {
        $this->beConstructedWith($repository);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(FilteredAssetReader::class);
    }

    function it_reads_assets(
        $repository,
        StepExecution $stepExecution,
        AssetInterface $asset,
        JobParameters $jobParameters
    ) {
        $this->setStepExecution($stepExecution);

        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('filters')->willReturn([['field' => 'id', 'operator' => 'IN', 'value' => [1,2,3]]]);

        $repository->find(1)->willReturn($asset);
        $stepExecution->incrementSummaryInfo('read')->shouldBecalled();

        $this->read()->shouldReturn($asset);
    }

    function it_does_not_read_assets(
        $repository,
        StepExecution $stepExecution,
        AssetInterface $asset,
        JobParameters $jobParameters
    ) {
        $this->setStepExecution($stepExecution);

        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('filters')->willReturn([['field' => 'id', 'operator' => 'IN', 'value' => [1,2,3]]]);

        $repository->find(1)->willReturn(null);
        $repository->find(2)->willReturn(null);
        $repository->find(3)->willReturn(null);
        $stepExecution->incrementSummaryInfo('read')->shouldNotBecalled();

        $this->read()->shouldReturn(null);
    }
}
