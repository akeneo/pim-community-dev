<?php

namespace spec\Akeneo\ActivityManager\Component\Normalizer;

use Akeneo\ActivityManager\Component\Model\ProjectInterface;
use Akeneo\ActivityManager\Component\Normalizer\ProjectNormalizer;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\DataGridBundle\Entity\DatagridView;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\LocaleInterface;
use PimEnterprise\Bundle\UserBundle\Entity\UserInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ProjectNormalizerSpec extends ObjectBehavior
{
    function let(NormalizerInterface $normalizer)
    {
        $this->beConstructedWith($normalizer);
    }

    function it_is_a_project_normalizer()
    {
        $this->shouldHaveType(ProjectNormalizer::class);
        $this->shouldImplement(NormalizerInterface::class);
    }

    function it_normalizes_a_project(
        $normalizer,
        ProjectInterface $project,
        \DateTime $datetime,
        UserInterface $user,
        ChannelInterface $channel,
        LocaleInterface $locale,
        DatagridView $datagridView
    ) {
        $datetime->format('Y-m-d')->willReturn('2069-02-15');
        $user->getId()->willReturn(42);

        $project->getLabel()->willReturn('Summer collection');
        $project->getDescription()->willReturn('The sun is here, such is the collection!');
        $project->getDueDate()->willReturn($datetime);
        $project->getOwner()->willReturn($user);
        $project->getChannel()->willReturn($channel);
        $project->getLocale()->willReturn($locale);
        $project->getDatagridView()->willReturn($datagridView);

        $normalizer->normalize($channel, 'standard', [])->willReturn(['code' => 'ecommerce']);
        $normalizer->normalize($locale, 'standard', [])->willReturn(['code' => 'fr_FR']);
        $normalizer->normalize($datagridView, 'internal_api', [])->willReturn(['label' => 'The OMG view']);

        $this->normalize($project, 'internal_api')->shouldReturn([
            'label' => 'Summer collection',
            'description' => 'The sun is here, such is the collection!',
            'due_date' => '2069-02-15',
            'owner' => 42,
            'channel' => ['code' => 'ecommerce'],
            'locale' => ['code' => 'fr_FR'],
            'datagridView' => ['label' => 'The OMG view']
        ]);
    }

    function it_throws_an_exception_if_object_to_normalize_is_not_a_project($object)
    {
        $this->shouldThrow('\InvalidArgumentException')->during('normalize', [$object]);
    }

    function it_specifies_that_the_normalizer_can_be_apply_on_a_project_with_the_internal_format(
        ProjectInterface $project,
        $object
    ) {
        $this->supportsNormalization($project, 'wrong_format')->shouldReturn(false);
        $this->supportsNormalization($object, 'internal_api')->shouldReturn(false);
        $this->supportsNormalization($object, 'wrong_format')->shouldReturn(false);
        $this->supportsNormalization($project, 'internal_api')->shouldReturn(true);
    }
}
