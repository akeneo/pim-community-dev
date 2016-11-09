<?php

namespace spec\Akeneo\Component\Batch\Normalizer\Standard;

use Akeneo\Component\Batch\Model\JobInstance;
use PhpSpec\ObjectBehavior;

class JobInstanceNormalizerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Akeneo\Component\Batch\Normalizer\Standard\JobInstanceNormalizer');
    }

    function it_is_a_normalizer()
    {
        $this->shouldImplement('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
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
        $jobinstance->getRawParameters()->willReturn(
            [
                'delimiter' => ';'
            ]
        );

        $this->normalize($jobinstance)->shouldReturn(
            [
                'code'          => 'product_export',
                'label'         => 'Product export',
                'connector'     => 'myconnector',
                'type'          => 'EXPORT',
                'configuration' => ['delimiter' => ';']
            ]
        );
    }
}
