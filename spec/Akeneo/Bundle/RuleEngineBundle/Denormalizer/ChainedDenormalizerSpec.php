<?php

namespace spec\Akeneo\Bundle\RuleEngineBundle\Denormalizer;

use PhpSpec\ObjectBehavior;
use Akeneo\Bundle\RuleEngineBundle\Model\RuleDefinitionInterface;
use Akeneo\Bundle\RuleEngineBundle\Runner\DryRunnerInterface;
use Akeneo\Bundle\RuleEngineBundle\Runner\RunnerInterface;
use PimEnterprise\Bundle\CatalogRuleBundle\Denormalizer\ProductRule\ContentDenormalizer;
use Prophecy\Argument;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class ChainedDenormalizerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Akeneo\Bundle\RuleEngineBundle\Denormalizer\ChainedDenormalizer');
    }

    function it_should_be_a_denormalizer()
    {
        $this->shouldHaveType('Symfony\Component\Serializer\Normalizer\DenormalizerInterface');
    }

    function it_supports_the_addition_of_denormalizers(DenormalizerInterface $denormalizer, ContentDenormalizer $contentDenormalizer)
    {
        $contentDenormalizer->setChainedDenormalizer(Argument::any())->shouldBeCalledTimes(1);

        $this->addDenormalizer($denormalizer);
        $this->addDenormalizer($contentDenormalizer);
    }

    function it_supports_denormalization(
        DenormalizerInterface $denormalizer1,
        DenormalizerInterface $denormalizer2
    ) {
        $ok = ['a data that can be denormalized'];
        $ko = ['a data that can NOT be denormalized'];

        $this->addDenormalizer($denormalizer1);
        $this->addDenormalizer($denormalizer2);

        $denormalizer1->supportsDenormalization($ok, Argument::cetera())->willReturn(false);
        $denormalizer2->supportsDenormalization($ok, Argument::cetera())->willReturn(true);
        $denormalizer1->supportsDenormalization($ko, Argument::cetera())->willReturn(false);
        $denormalizer2->supportsDenormalization($ko, Argument::cetera())->willReturn(false);

        $this->supportsDenormalization($ok, Argument::cetera())->shouldReturn(true);
        $this->supportsDenormalization($ko, Argument::cetera())->shouldReturn(false);
    }

    function it_denormalizes(
        DenormalizerInterface $denormalizer1,
        DenormalizerInterface $denormalizer2,
        \StdClass $denoramlizedData
    ) {
        $data = ['a data that can be denormalized'];

        $this->addDenormalizer($denormalizer1);
        $this->addDenormalizer($denormalizer2);

        $denormalizer1->supportsDenormalization($data, Argument::cetera())->willReturn(false);
        $denormalizer2->supportsDenormalization($data, Argument::cetera())->willReturn(true);
        $denormalizer2->denormalize($data, Argument::cetera())->willReturn($denoramlizedData);

        $this->denormalize($data, Argument::cetera())->shouldReturn($denoramlizedData);
    }

    function it_throws_an_exception_when_no_denormalizer_supports_the_data()
    {
        $this->shouldThrow(new \LogicException('No denormalizer able to denormalize the data.'))
            ->during('denormalize', [Argument::any(), Argument::any()]);
    }
}
