<?php

namespace Specification\Akeneo\UserManagement\Bundle\EventListener;

use Akeneo\Channel\Component\Model\ChannelInterface;
use Akeneo\Channel\Component\Model\LocaleInterface;
use Akeneo\Channel\Component\Repository\ChannelRepositoryInterface;
use Akeneo\Tool\Component\Classification\Model\CategoryInterface;
use Akeneo\Tool\Component\Classification\Repository\CategoryRepositoryInterface;
use Akeneo\UserManagement\Bundle\EventListener\UserPreferencesSubscriber;
use Akeneo\UserManagement\Bundle\Manager\UserManager;use Akeneo\UserManagement\Component\Model\UserInterface;use Akeneo\UserManagement\Component\Repository\UserRepositoryInterface;use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\UnitOfWork;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\DependencyInjection\ContainerInterface;

class UserPreferencesSubscriberSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(UserPreferencesSubscriber::class);
    }

    function it_implements_an_event_subscriber()
    {
        $this->shouldImplement(EventSubscriber::class);
    }

    function it_returns_events()
    {
        $this->getSubscribedEvents()->shouldReturn([
            'onFlush',
            'postFlush',
        ]);
    }

    function it_deletes_a_tree_before_flush(
        OnFlushEventArgs $args,
        EntityManagerInterface $em,
        UnitOfWork $uow,
        ContainerInterface $container,
        UserManager $userManager,
        UserRepositoryInterface $userRepository,
        CategoryInterface $masterCategory,
        CategoryInterface $summerCategory,
        UserInterface $mary,
        UserInterface $julia,
        CategoryRepositoryInterface $categoryRepository,
        ClassMetadata $metadata
    ) {
        $this->setContainer($container);
        $container->get('pim_user.manager')->willReturn($userManager);

        $args->getEntityManager()->willReturn($em);
        $em->getUnitOfWork()->willReturn($uow);
        $uow->getScheduledEntityUpdates()->willReturn([]);
        $uow->getScheduledEntityDeletions()->willReturn([$masterCategory]);

        $masterCategory->isRoot()->willReturn(true);

        $userManager->getRepository()->willReturn($userRepository);
        $userRepository->findBy(['defaultTree' => $masterCategory])->willReturn([$mary, $julia]);

        $container->get('pim_catalog.repository.category')->willReturn($categoryRepository);
        $categoryRepository->getTrees()->willReturn([$summerCategory, $masterCategory]);

        $summerCategory->getCode()->willReturn('summer');
        $masterCategory->getCode()->willReturn('master');

        $julia->setDefaultTree($summerCategory)->shouldBeCalled();
        $mary->setDefaultTree($summerCategory)->shouldBeCalled();

        $uow->persist($julia)->shouldBeCalled();
        $em->getClassMetadata(Argument::any())->willReturn($metadata);
        $uow->computeChangeSet($metadata, $julia)->shouldBeCalled();
        $uow->persist($mary)->shouldBeCalled();
        $uow->computeChangeSet($metadata, $mary)->shouldBeCalled();

        $this->onFlush($args)->shouldReturn(null);
    }

    function it_deletes_a_channe_before_flush(
        OnFlushEventArgs $args,
        EntityManagerInterface $em,
        UnitOfWork $uow,
        ContainerInterface $container,
        UserManager $userManager,
        UserRepositoryInterface $userRepository,
        ChannelInterface $ecommerceChannel,
        ChannelInterface $printChannel,
        UserInterface $mary,
        ChannelRepositoryInterface $channelRepository,
        ClassMetadata $metadata
    ) {
        $this->setContainer($container);
        $container->get('pim_user.manager')->willReturn($userManager);

        $args->getEntityManager()->willReturn($em);
        $em->getUnitOfWork()->willReturn($uow);
        $uow->getScheduledEntityUpdates()->willReturn([]);
        $uow->getScheduledEntityDeletions()->willReturn([$ecommerceChannel]);

        $userManager->getRepository()->willReturn($userRepository);
        $userRepository->findBy(['catalogScope' => $ecommerceChannel])->willReturn([$mary]);

        $container->get('pim_catalog.repository.channel')->willReturn($channelRepository);
        $channelRepository->findAll()->willReturn([$ecommerceChannel, $printChannel]);

        $ecommerceChannel->getCode()->willReturn('ecommerce');
        $printChannel->getCode()->willReturn('print');

        $mary->setCatalogScope($printChannel)->shouldBeCalled();

        $em->getClassMetadata(Argument::any())->willReturn($metadata);
        $uow->persist($mary)->shouldBeCalled();
        $uow->computeChangeSet($metadata, $mary)->shouldBeCalled();

        $this->onFlush($args)->shouldReturn(null);
    }
}
