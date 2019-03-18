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

use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\Model\Read\Family;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\Repository\FamilyRepositoryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Persistence\Query\Doctrine\SelectUserIdsAndFamilyCodesWithMissingMappingQuery;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\UserNotification\NotifyUserAboutMissingMapping;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Akeneo\UserManagement\Component\Repository\UserRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class NotifyPendingAttributesTaskletSpec extends ObjectBehavior
{
    public function let(
        SelectUserIdsAndFamilyCodesWithMissingMappingQuery $selectUserAndFamilyIdsQuery,
        UserRepositoryInterface $userRepository,
        FamilyRepositoryInterface $familyRepository,
        NotifyUserAboutMissingMapping $notifyUserAboutMissingMapping
    ): void {
        $this->beConstructedWith(
            $selectUserAndFamilyIdsQuery,
            $userRepository,
            $familyRepository,
            $notifyUserAboutMissingMapping
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
        $notifyUserAboutMissingMapping,
        UserInterface $julia,
        UserInterface $julien,
        Family $familyA,
        Family $familyB,
        Family $familyC
    ): void {
        $selectUserAndFamilyIdsQuery->execute()->willReturn([
            1 => ['family_42', 'family_43'],
            2 => ['family_43', 'family_44'],
        ]);

        $userRepository->find(1)->willReturn($julia);
        $userRepository->find(2)->willReturn($julien);

        $familyRepository->findOneByIdentifier('family_42')->willReturn($familyA);
        $familyRepository->findOneByIdentifier('family_43')->willReturn($familyB);
        $familyRepository->findOneByIdentifier('family_44')->willReturn($familyC);

        $notifyUserAboutMissingMapping->forFamily(Argument::cetera())->shouldBeCalledTimes(4);
        $notifyUserAboutMissingMapping->forFamily($julia, $familyA)->shouldBeCalled();
        $notifyUserAboutMissingMapping->forFamily($julia, $familyB)->shouldBeCalled();
        $notifyUserAboutMissingMapping->forFamily($julien, $familyB)->shouldBeCalled();
        $notifyUserAboutMissingMapping->forFamily($julien, $familyC)->shouldBeCalled();

        $this->execute();
    }

    public function it_does_not_notify_if_user_id_does_not_correspond_to_an_existing_user(
        $selectUserAndFamilyIdsQuery,
        $userRepository,
        $familyRepository,
        $notifyUserAboutMissingMapping,
        UserInterface $julia,
        Family $familyA
    ): void {
        $selectUserAndFamilyIdsQuery->execute()->willReturn([
            1 => ['family_42'],
            2 => ['family_43'],
        ]);

        $userRepository->find(1)->willReturn($julia);
        $userRepository->find(2)->willReturn(null);

        $familyRepository->findOneByIdentifier('family_42')->willReturn($familyA);

        $notifyUserAboutMissingMapping->forFamily(Argument::cetera())->shouldBeCalledTimes(1);
        $notifyUserAboutMissingMapping->forFamily($julia, $familyA)->shouldBeCalled();

        $this->execute();
    }

    public function it_does_not_notify_if_family_id_does_not_correspond_to_an_existing_family(
        $selectUserAndFamilyIdsQuery,
        $userRepository,
        $familyRepository,
        $notifyUserAboutMissingMapping,
        UserInterface $julia
    ): void {
        $selectUserAndFamilyIdsQuery->execute()->willReturn([1 => ['family_42']]);

        $userRepository->find(1)->willReturn($julia);

        $familyRepository->findOneByIdentifier('family_42')->willReturn(null);

        $notifyUserAboutMissingMapping->forFamily(Argument::cetera())->shouldNotBeCalled();

        $this->execute();
    }
}
