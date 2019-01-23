<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Connector\Tasklet;

use Akeneo\Channel\Component\Model\Locale;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Persistence\Query\Doctrine\SelectUserAndFamilyIdsWithMissingMappingQuery;
use Akeneo\Pim\Structure\Component\Model\Family;
use Akeneo\Pim\Structure\Component\Model\FamilyTranslation;
use Akeneo\Pim\Structure\Component\Repository\FamilyRepositoryInterface;
use Akeneo\Platform\Bundle\NotificationBundle\Entity\NotificationInterface;
use Akeneo\Platform\Bundle\NotificationBundle\NotifierInterface;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;
use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\UserManagement\Component\Model\User;
use Akeneo\UserManagement\Component\Repository\UserRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class NotifyIfPendingAttributesTaskletSpec extends ObjectBehavior
{
    public function let(
        SelectUserAndFamilyIdsWithMissingMappingQuery $selectUserAndFamilyIdsQuery,
        UserRepositoryInterface $userRepository,
        FamilyRepositoryInterface $familyRepository,
        SimpleFactoryInterface $notificationFactory,
        NotifierInterface $notifier
    ): void {
        $this->beConstructedWith(
            $selectUserAndFamilyIdsQuery,
            $userRepository,
            $familyRepository,
            $notificationFactory,
            $notifier
        );
    }

    public function it_is_a_tasklet(): void
    {
        $this->shouldImplement(TaskletInterface::class);
    }

    public function it_notifies_users_they_have_new_attributes_to_map(
        $selectUserAndFamilyIdsQuery,
        $userRepository,
        $familyRepository,
        $notificationFactory,
        $notifier,
        NotificationInterface $notificationForJuliaAboutFamilyA,
        NotificationInterface $notificationForJuliaAboutFamilyB,
        NotificationInterface $notificationForJulienAboutFamilyB,
        NotificationInterface $notificationForJulienAboutFamilyC
    ): void {
        $selectUserAndFamilyIdsQuery->execute()->willReturn([
            1 => [42, 43],
            2 => [43, 44],
        ]);

        $julia = new User();
        $julia->setUsername('julia');
        $julia->setUiLocale((new Locale())->setCode('en_US'));
        $userRepository->find(1)->willReturn($julia);

        $julien = new User();
        $julien->setUsername('julien');
        $julien->setUiLocale((new Locale())->setCode('fr_FR'));
        $userRepository->find(2)->willReturn($julien);

        $familyA = new Family();
        $familyA->setCode('familyA');
        $familyA->addTranslation((new FamilyTranslation())->setLocale('en_US')->setLabel('Family A'));
        $familyRepository->find(42)->willReturn($familyA);

        $familyB = new Family();
        $familyB->setCode('familyB');
        $familyB->addTranslation((new FamilyTranslation())->setLocale('en_US')->setLabel('Family B'));
        $familyRepository->find(43)->willReturn($familyB);

        $familyC = new Family();
        $familyC->setCode('familyC');
        $familyC->addTranslation((new FamilyTranslation())->setLocale('fr_FR')->setLabel('Famille C'));
        $familyRepository->find(44)->willReturn($familyC);

        $notificationFactory->create()->willReturn(
            $notificationForJuliaAboutFamilyA,
            $notificationForJuliaAboutFamilyB,
            $notificationForJulienAboutFamilyB,
            $notificationForJulienAboutFamilyC
        );

        $notificationForJuliaAboutFamilyA->setType('success')->willReturn($notificationForJuliaAboutFamilyA);
        $notificationForJuliaAboutFamilyA
            ->setMessage('akeneo_franklin_insights.entity.attributes_mapping.notification.new_attributes_to_map')
            ->willReturn($notificationForJuliaAboutFamilyA);
        $notificationForJuliaAboutFamilyA
            ->setMessageParams(['familyLabel' => 'Family A'])
            ->willReturn($notificationForJuliaAboutFamilyA);
        $notificationForJuliaAboutFamilyA
            ->setRoute('akeneo_franklin_insights_attributes_mapping_edit')
            ->willReturn($notificationForJuliaAboutFamilyA);
        $notificationForJuliaAboutFamilyA
            ->setRouteParams(['familyCode' => 'familyA'])
            ->willReturn($notificationForJuliaAboutFamilyA);

        $notificationForJuliaAboutFamilyB->setType('success')->willReturn($notificationForJuliaAboutFamilyB);
        $notificationForJuliaAboutFamilyB
            ->setMessage('akeneo_franklin_insights.entity.attributes_mapping.notification.new_attributes_to_map')
            ->willReturn($notificationForJuliaAboutFamilyB);
        $notificationForJuliaAboutFamilyB
            ->setMessageParams(['familyLabel' => 'Family B'])
            ->willReturn($notificationForJuliaAboutFamilyB);
        $notificationForJuliaAboutFamilyB
            ->setRoute('akeneo_franklin_insights_attributes_mapping_edit')
            ->willReturn($notificationForJuliaAboutFamilyB);
        $notificationForJuliaAboutFamilyB
            ->setRouteParams(['familyCode' => 'familyB'])
            ->willReturn($notificationForJuliaAboutFamilyB);

        $notificationForJulienAboutFamilyB->setType('success')->willReturn($notificationForJulienAboutFamilyB);
        $notificationForJulienAboutFamilyB
            ->setMessage('akeneo_franklin_insights.entity.attributes_mapping.notification.new_attributes_to_map')
            ->willReturn($notificationForJulienAboutFamilyB);
        $notificationForJulienAboutFamilyB
            ->setMessageParams(['familyLabel' => '[familyB]'])
            ->willReturn($notificationForJulienAboutFamilyB);
        $notificationForJulienAboutFamilyB
            ->setRoute('akeneo_franklin_insights_attributes_mapping_edit')
            ->willReturn($notificationForJulienAboutFamilyB);
        $notificationForJulienAboutFamilyB
            ->setRouteParams(['familyCode' => 'familyB'])
            ->willReturn($notificationForJulienAboutFamilyB);

        $notificationForJulienAboutFamilyC->setType('success')->willReturn($notificationForJulienAboutFamilyC);
        $notificationForJulienAboutFamilyC
            ->setMessage('akeneo_franklin_insights.entity.attributes_mapping.notification.new_attributes_to_map')
            ->willReturn($notificationForJulienAboutFamilyC);
        $notificationForJulienAboutFamilyC
            ->setMessageParams(['familyLabel' => 'Famille C'])
            ->willReturn($notificationForJulienAboutFamilyC);
        $notificationForJulienAboutFamilyC
            ->setRoute('akeneo_franklin_insights_attributes_mapping_edit')
            ->willReturn($notificationForJulienAboutFamilyC);
        $notificationForJulienAboutFamilyC
            ->setRouteParams(['familyCode' => 'familyC'])
            ->willReturn($notificationForJulienAboutFamilyC);

        $notifier->notify(Argument::cetera())->shouldBeCalledTimes(4);
        $notifier->notify($notificationForJuliaAboutFamilyA, ['julia'])->shouldBeCalled();
        $notifier->notify($notificationForJuliaAboutFamilyB, ['julia'])->shouldBeCalled();
        $notifier->notify($notificationForJulienAboutFamilyB, ['julien'])->shouldBeCalled();
        $notifier->notify($notificationForJulienAboutFamilyC, ['julien'])->shouldBeCalled();

        $this->execute();
    }

    public function it_does_not_notify_if_user_id_does_not_correspond_to_an_existing_user(
        $selectUserAndFamilyIdsQuery,
        $userRepository,
        $familyRepository,
        $notificationFactory,
        $notifier,
        NotificationInterface $notificationForJuliaAboutFamilyA
    ): void {
        $selectUserAndFamilyIdsQuery->execute()->willReturn([
            1 => [42],
            2 => [43],
        ]);

        $julia = new User();
        $julia->setUsername('julia');
        $julia->setUiLocale((new Locale())->setCode('en_US'));
        $userRepository->find(1)->willReturn($julia);

        $userRepository->find(2)->willReturn(null);

        $familyA = new Family();
        $familyA->setCode('familyA');
        $familyA->addTranslation((new FamilyTranslation())->setLocale('en_US')->setLabel('Family A'));
        $familyRepository->find(42)->willReturn($familyA);

        $notificationFactory->create()->willReturn($notificationForJuliaAboutFamilyA);

        $notificationForJuliaAboutFamilyA->setType('success')->willReturn($notificationForJuliaAboutFamilyA);
        $notificationForJuliaAboutFamilyA
            ->setMessage('akeneo_franklin_insights.entity.attributes_mapping.notification.new_attributes_to_map')
            ->willReturn($notificationForJuliaAboutFamilyA);
        $notificationForJuliaAboutFamilyA
            ->setMessageParams(['familyLabel' => 'Family A'])
            ->willReturn($notificationForJuliaAboutFamilyA);
        $notificationForJuliaAboutFamilyA
            ->setRoute('akeneo_franklin_insights_attributes_mapping_edit')
            ->willReturn($notificationForJuliaAboutFamilyA);
        $notificationForJuliaAboutFamilyA
            ->setRouteParams(['familyCode' => 'familyA'])
            ->willReturn($notificationForJuliaAboutFamilyA);

        $notifier->notify(Argument::cetera())->shouldBeCalledTimes(1);
        $notifier->notify($notificationForJuliaAboutFamilyA, ['julia'])->shouldBeCalled();

        $this->execute();
    }

    public function it_does_not_notify_if_family_id_does_not_correspond_to_an_existing_family(
        $selectUserAndFamilyIdsQuery,
        $userRepository,
        $familyRepository,
        $notificationFactory,
        $notifier
    ): void {
        $selectUserAndFamilyIdsQuery->execute()->willReturn([1 => [42],]);

        $julia = new User();
        $julia->setUsername('julia');
        $julia->setUiLocale((new Locale())->setCode('en_US'));
        $userRepository->find(1)->willReturn($julia);

        $familyRepository->find(42)->willReturn(null);

        $notificationFactory->create()->shouldNotBeCalled();
        $notifier->notify(Argument::cetera())->shouldNotBeCalled();

        $this->execute();
    }
}
