<?php

namespace Specification\Akeneo\Channel\Bundle\Doctrine\Remover;

use Akeneo\Channel\Component\Model\Channel;
use Akeneo\Tool\Component\StorageUtils\Remover\RemoverInterface;
use Doctrine\Common\Persistence\ObjectManager;
use PhpSpec\ObjectBehavior;
use Akeneo\Channel\Component\Repository\ChannelRepositoryInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Translation\TranslatorInterface;

class ChannelRemoverSpec extends ObjectBehavior
{
    function let(
        ObjectManager $objectManager,
        EventDispatcherInterface $eventDispatcher,
        ChannelRepositoryInterface $channelRepository,
        TranslatorInterface $translator
    ) {
        $this->beConstructedWith(
            $objectManager,
            $eventDispatcher,
            $channelRepository,
            $translator,
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
        ChannelRepositoryInterface $channelRepository
    ) {
        $channelRepository->countAll()->willReturn(2);
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
}
