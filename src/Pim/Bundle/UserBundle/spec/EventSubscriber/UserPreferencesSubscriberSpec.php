<?php

namespace spec\Pim\Bundle\UserBundle\EventSubscriber;

use Akeneo\Component\Classification\Repository\CategoryRepositoryInterface;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\UnitOfWork;
use Oro\Bundle\UserBundle\Entity\UserManager;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\UserBundle\Entity\UserInterface;
use Pim\Bundle\UserBundle\EventSubscriber\UserPreferencesSubscriber;
use Pim\Bundle\UserBundle\Repository\UserRepositoryInterface;
use Pim\Component\Catalog\Model\CategoryInterface;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\LocaleInterface;
use Pim\Component\Catalog\Repository\ChannelRepositoryInterface;
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

    function it_deactivates_a_locale_before_flush(
        OnFlushEventArgs $args,
        EntityManagerInterface $em,
        UnitOfWork $uow,
        LocaleInterface $localeFR,
        LocaleInterface $localeEN
    ) {
        $args->getEntityManager()->willReturn($em);
        $em->getUnitOfWork()->willReturn($uow);
        $uow->getScheduledEntityUpdates()->willReturn([$localeFR, $localeEN]);
        $uow->getScheduledEntityDeletions()->willReturn([]);

        $localeFR->isActivated()->willReturn(true);
        $localeFR->getCode()->shouldNotBeCalled();
        $uow->getEntityChangeSet($localeFR)->shouldNotBeCalled();

        $localeEN->isActivated()->willReturn(false);
        $localeEN->getCode()->willReturn('en_US');
        $uow->getEntityChangeSet($localeEN)->willReturn(['activated' => true]);


        $this->onFlush($args)->shouldReturn(null);
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
        $container->get('oro_user.manager')->willReturn($userManager);

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
        $container->get('oro_user.manager')->willReturn($userManager);

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
