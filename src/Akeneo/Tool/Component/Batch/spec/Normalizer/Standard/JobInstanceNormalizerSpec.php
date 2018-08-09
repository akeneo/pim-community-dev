<?php

namespace spec\Akeneo\Tool\Component\Batch\Normalizer\Standard;

use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\Tool\Component\Batch\Normalizer\Standard\JobInstanceNormalizer;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class JobInstanceNormalizerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(JobInstanceNormalizer::class);
    }

    function it_is_a_normalizer()
    {
        $this->shouldImplement(NormalizerInterface::class);
    }

    function it_supports_job_instance_normalization_into_json_and_xml(JobInstance $jobinstance)
    {
        $this->supportsNormalization($jobinstance, 'csv')->shouldBe(false);
        $this->supportsNormalization($jobinstance, 'json')->shouldBe(false);
        $this->supportsNormalization($jobinstance, 'xml')->shouldBe(false);
        $this->supportsNormalization($jobinstance, 'standard')->shouldBe(true);
    }

    function it_normalizes_job_instance(JobInstance $jobinstance)
    {
        $jobinstance->getCode()->willReturn('product_export');
        $jobinstance->getLabel()->willReturn('Product export');
        $jobinstance->getConnector()->willReturn('myconnector');
        $jobinstance->getType()->willReturn('EXPORT');
        $jobinstance->getJobName()->willReturn('csv_product_export');
        $jobinstance->getRawParameters()->willReturn(
            [
                'delimiter' => ';'
            ]
        );

        $this->normalize($jobinstance)->shouldReturn(
            [
                'code'          => 'product_export',
                'job_name'      => 'csv_product_export',
                'label'         => 'Product export',
                'connector'     => 'myconnector',
                'type'          => 'EXPORT',
                'configuration' => ['delimiter' => ';']
            ]
        );
    }
}
