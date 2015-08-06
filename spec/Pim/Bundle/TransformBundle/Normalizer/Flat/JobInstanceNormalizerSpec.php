<?php

namespace spec\Pim\Bundle\TransformBundle\Normalizer\Flat;

use Akeneo\Bundle\BatchBundle\Entity\JobInstance;
use PhpSpec\ObjectBehavior;

class JobInstanceNormalizerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\TransformBundle\Normalizer\Flat\JobInstanceNormalizer');
    }

    function it_is_a_normalizer()
    {
        $this->shouldImplement('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
    }

    function it_supports_job_instance_normalization_into_csv(JobInstance $jobinstance)
    {
        $this->supportsNormalization($jobinstance, 'csv')->shouldBe(true);
        $this->supportsNormalization($jobinstance, 'json')->shouldBe(false);
        $this->supportsNormalization($jobinstance, 'xml')->shouldBe(false);
    }

    function it_normalizes_job_instance(JobInstance $jobinstance)
    {
        $jobinstance->getCode()->willReturn('product_export');
        $jobinstance->getLabel()->willReturn('Product export');
        $jobinstance->getConnector()->willReturn('myconnector');
        $jobinstance->getType()->willReturn('EXPORT');
        $jobinstance->getRawConfiguration()->willReturn(
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
                'configuration' => '{"delimiter":";"}'
            ]
        );
    }
}
