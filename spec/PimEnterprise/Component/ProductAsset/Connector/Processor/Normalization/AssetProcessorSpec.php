<?php

namespace spec\PimEnterprise\Component\ProductAsset\Connector\Processor\Normalization;

use Akeneo\Component\Batch\Job\JobParameters;
use Akeneo\Component\Batch\Model\StepExecution;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Repository\LocaleRepositoryInterface;
use PimEnterprise\Component\ProductAsset\Model\AssetInterface;
use Prophecy\Argument;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;

class AssetProcessorSpec extends ObjectBehavior
{
    function let(
        SerializerInterface $serializer,
        LocaleRepositoryInterface $localeRepository,
        NormalizerInterface $assetNormalizer,
        StepExecution $stepExecution
    ) {
        $this->beConstructedWith($serializer, $localeRepository, $assetNormalizer);
        $this->setStepExecution($stepExecution);
    }

    function it_is_a_configurable_step_execution_aware_processor()
    {
        $this->shouldImplement('Pim\Bundle\BaseConnectorBundle\Processor\CsvSerializer\Processor');
    }

    function it_processes(
        $assetNormalizer,
        $serializer,
        $localeRepository,
        $stepExecution,
        AssetInterface $asset,
        JobParameters $jobParameters
    ) {
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('delimiter')->willReturn(';');
        $jobParameters->get('enclosure')->willReturn('"');
        $jobParameters->get('withHeader')->willReturn(true);

        $values = [
            'code'        => 'mycode',
            'localized'   => 0,
            'description' => 'My awesome description',
            'categories'  => 'cat1,cat2,cat3',
            'tags'        => 'dog,flowers',
            'end_of_use'  => '2018/02/01',
        ];

        $result = [
            'code,localized,description,categories,tags,end_of_use',
            'mycode;0;"My awesome description";cat1,cat2,cat3;dog,flowers;2018/02/01'
        ];
        $assetNormalizer->normalize($asset)->willReturn($values);
        $serializer->serialize($values, 'csv',
            [
                'delimiter'     => ';',
                'enclosure'     => '"',
                'withHeader'    => true,
                'heterogeneous' => false,
                'locales'       => 'en_US',
            ])->willReturn($result);

        $localeRepository->getActivatedLocaleCodes()->willReturn('en_US');

        $this
            ->process($asset)
            ->shouldReturn($result);
    }
}
