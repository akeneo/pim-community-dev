<?php

namespace Specification\Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Normalizer;

use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Model\ProjectInterface;
use PhpSpec\ObjectBehavior;
use Oro\Bundle\PimDataGridBundle\Entity\DatagridView;
use Akeneo\Channel\Infrastructure\Component\Model\ChannelInterface;
use Akeneo\Channel\Infrastructure\Component\Model\LocaleInterface;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Normalizer\ProjectNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Serializer;

class ProjectNormalizerSpec extends ObjectBehavior
{
    function it_is_a_project_normalizer()
    {
        $this->shouldHaveType(ProjectNormalizer::class);
        $this->shouldImplement(NormalizerInterface::class);
    }

    function it_normalizes_a_project(
        ProjectInterface $project,
        \DateTime $datetime,
        UserInterface $user,
        ChannelInterface $channel,
        LocaleInterface $locale,
        DatagridView $datagridView,
        Serializer $serializer
    ) {
        $this->setSerializer($serializer);

        $datetime->format('Y-m-d')->willReturn('2069-02-15');
        $user->getId()->willReturn(42);

        $project->getLabel()->willReturn('Summer collection');
        $project->getCode()->willReturn('summer-collection-ecommerce-fr-fr');
        $project->getDescription()->willReturn('The sun is here, such is the collection!');
        $project->getDueDate()->willReturn($datetime);
        $project->getOwner()->willReturn($user);
        $project->getChannel()->willReturn($channel);
        $project->getLocale()->willReturn($locale);
        $project->getDatagridView()->willReturn($datagridView);

        $serializer->normalize($user, 'internal_api', [])->willReturn(['name' => 'pipou']);
        $serializer->normalize($channel, 'internal_api', [])->willReturn(['code' => 'ecommerce']);
        $serializer->normalize($locale, 'internal_api', [])->willReturn(['code' => 'fr_FR']);
        $serializer->normalize($datagridView, 'internal_api', [])->willReturn(['label' => 'The OMG view']);

        $this->normalize($project, 'internal_api')->shouldReturn([
            'label' => 'Summer collection',
            'code' => 'summer-collection-ecommerce-fr-fr',
            'description' => 'The sun is here, such is the collection!',
            'due_date' => '2069-02-15',
            'owner' => ['name' => 'pipou'],
            'channel' => ['code' => 'ecommerce'],
            'locale' => ['code' => 'fr_FR'],
            'datagridView' => ['label' => 'The OMG view'],
        ]);
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
