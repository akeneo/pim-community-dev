<?php

namespace Specification\Akeneo\Channel\Bundle\Doctrine\Remover;

use Akeneo\Channel\Component\Model\Channel;
use Akeneo\Channel\Component\Query\IsChannelUsedInProductExportJobInterface;
use Akeneo\Tool\Component\StorageUtils\Remover\RemoverInterface;
use Doctrine\Common\Persistence\ObjectManager;
use PhpSpec\ObjectBehavior;
use Akeneo\Channel\Component\Repository\ChannelRepositoryInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class ChannelRemoverSpec extends ObjectBehavior
{
    function let(
        ObjectManager $objectManager,
        EventDispatcherInterface $eventDispatcher,
        ChannelRepositoryInterface $channelRepository,
        TranslatorInterface $translator,
        IsChannelUsedInProductExportJobInterface $isChannelUsedInProductExportJob
    ) {
        $this->beConstructedWith(
            $objectManager,
            $eventDispatcher,
            $channelRepository,
            $translator,
            $isChannelUsedInProductExportJob,
            Channel::class
        );
    }

    function it_is_a_remover()
    {
        $this->shouldImplement(RemoverInterface::class);
    }

    function it_removes_the_channel_and_flushes_the_unit_of_work(
        $objectManager,
        Channel $channel,
        ChannelRepositoryInterface $channelRepository,
        IsChannelUsedInProductExportJobInterface $isChannelUsedInProductExportJob
    ) {
        $channel->getId()->willReturn(42);
        $channel->getCode()->willReturn('mobile');

        $channelRepository->countAll()->willReturn(2);
        $isChannelUsedInProductExportJob->execute('mobile')->willReturn(false);

        $objectManager->remove($channel)->shouldBeCalled();
        $objectManager->flush()->shouldBeCalled();
        $this->remove($channel);
    }

    function it_throws_invalid_argument_exception_when_given_class_is_not_channel()
    {
        $anythingElse = new \stdClass();
        $exception = new \InvalidArgumentException(
            sprintf(
            'Expects a "Akeneo\Channel\Component\Model\Channel", "%s" provided.',
                get_class($anythingElse)
            )
        );

        $this->shouldThrow($exception)->during('remove', [$anythingElse]);
    }

    function it_throws_logic_exception_when_only_one_channel_left_in_repository(
        Channel $channel,
        ChannelRepositoryInterface $channelRepository,
        TranslatorInterface $translator
    ) {
        $channelRepository->countAll()->willReturn(1);
        $channel->getCode()->willReturn('code');
        $translator->trans('pim_enrich.channel.flash.delete.error', ['%channelCode%' => 'code'])->willReturn('exception message');
        $logicException = new \LogicException('exception message');

        $this->shouldThrow($logicException)->during('remove', [$channel]);
    }

    public function it_throws_logic_exception_when_the_channel_is_used_in_an_export_profile(
        Channel $channel,
        ChannelRepositoryInterface $channelRepository,
        TranslatorInterface $translator,
        IsChannelUsedInProductExportJobInterface $isChannelUsedInProductExportJob
    ) {
        $channel->getCode()->willReturn('mobile');

        $channelRepository->countAll()->willReturn(2);
        $isChannelUsedInProductExportJob->execute('mobile')->willReturn(true);

        $translator->trans('pim_enrich.channel.flash.delete.linked_to_export_profile')->willReturn('exception message');
        $logicException = new \LogicException('exception message');

        $this->shouldThrow($logicException)->during('remove', [$channel]);
    }
}
