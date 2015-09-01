<?php

namespace spec\PimEnterprise\Bundle\ProductAssetBundle\Validator\Constraints;

use Akeneo\Component\FileTransformer\Exception\InvalidOptionsTransformationException;
use Akeneo\Component\FileTransformer\Exception\NonRegisteredTransformationException;
use Akeneo\Component\FileTransformer\Transformation\TransformationInterface;
use Akeneo\Component\FileTransformer\Transformation\TransformationRegistry;
use Doctrine\Common\Collections\ArrayCollection;
use PhpSpec\ObjectBehavior;
use PimEnterprise\Bundle\ProductAssetBundle\Validator\Constraints\ChannelConfiguration;
use PimEnterprise\Component\ProductAsset\Model\ChannelVariationsConfigurationInterface;
use Prophecy\Argument;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class ChannelConfigurationValidatorSpec extends ObjectBehavior
{
    function let(TransformationRegistry $registry, ExecutionContextInterface $context)
    {
        $this->beConstructedWith($registry);
        $this->initialize($context);
    }

    function it_does_not_validate_if_object_is_not_a_channel_configuration(
        $context,
        ChannelConfiguration $constraint
    ) {
        $object = new \stdClass();
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate($object, $constraint);
    }

    function it_adds_violations_if_the_channel_configuration_is_not_valid(
        $context,
        $registry,
        ChannelVariationsConfigurationInterface $channelConfiguration,
        ChannelConfiguration $constraint,
        TransformationInterface $transformation,
        OptionsResolver $transformationResolver,
        ConstraintViolationBuilderInterface $violation
    ) {
        $erroneousConfiguration = ['scale' => ['wrongField' => 0.5]];
        $channelConfiguration->getConfiguration()->willReturn($erroneousConfiguration);

        $registry->get('scale', 'image/jpeg')->willReturn($transformation);
        $transformation->getOptionsResolver()->willReturn($transformationResolver);
        $transformationResolver->resolve(['wrongField' => 0.5])
            ->willThrow(new InvalidOptionsTransformationException('errorMsg'));

        $context->buildViolation(
            $constraint->invalidConfiguration,
            ['%transformation%' => 'scale', '%error%' => 'errorMsg']
        )
        ->shouldBeCalled()
        ->willReturn($violation);

        $this->validate($channelConfiguration, $constraint);
    }

    function it_adds_violations_if_the_channel_configuration_contains_an_unknown_transformation(
        $context,
        $registry,
        ChannelVariationsConfigurationInterface $channelConfiguration,
        ChannelConfiguration $constraint,
        ConstraintViolationBuilderInterface $violation
    ) {
        $erroneousConfiguration = ['unexistingTransfo' => ['wrongField' => 0.5]];
        $channelConfiguration->getConfiguration()->willReturn($erroneousConfiguration);

        $registry->get('unexistingTransfo', 'image/jpeg')
            ->willThrow(new NonRegisteredTransformationException('transformation', 'mimeType', 'errorMsg'));

        $context->buildViolation(
            $constraint->unknownTransformation,
            ['%transformation%' => 'unexistingTransfo']
        )
        ->shouldBeCalled()
        ->willReturn($violation);

        $this->validate($channelConfiguration, $constraint);
    }
}
