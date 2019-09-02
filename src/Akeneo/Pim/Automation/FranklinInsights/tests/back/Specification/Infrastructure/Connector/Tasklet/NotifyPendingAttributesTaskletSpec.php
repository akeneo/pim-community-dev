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
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Persistence\Query\Doctrine\FilterUsersToNotifyAboutGivenFamilyMissingMappingQuery;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Persistence\Query\Doctrine\SelectUsersAbleToCompleteFamiliesMissingMappingQuery;
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
        SelectUsersAbleToCompleteFamiliesMissingMappingQuery $userIdsAndFamilyCodesQuery,
        UserRepositoryInterface $userRepository,
        FamilyRepositoryInterface $familyRepository,
        NotifyUserAboutMissingMapping $notifyUserAboutMissingMapping,
        FilterUsersToNotifyAboutGivenFamilyMissingMappingQuery $filterUsersToNotify
    ): void {
        $this->beConstructedWith(
            $userIdsAndFamilyCodesQuery,
            $userRepository,
            $familyRepository,
            $notifyUserAboutMissingMapping,
            $filterUsersToNotify
        );
    }

    public function it_is_a_tasklet(): void
    {
        $this->shouldImplement(TaskletInterface::class);
    }

    public function it_notifies_users_they_have_new_attributes_to_map(
        $userIdsAndFamilyCodesQuery,
        $userRepository,
        $familyRepository,
        $notifyUserAboutMissingMapping,
        $filterUsersToNotify,
        UserInterface $julia,
        UserInterface $julien,
        UserInterface $judith,
        Family $family42,
        Family $familyABC
    ): void {
        $userIdsAndFamilyCodesQuery->execute()->willReturn([
            'family_42' => [1, 2],
            'family_ABC' => [1, 2, 3],
        ]);

        $userRepository->find(1)->willReturn($julia);
        $userRepository->find(2)->willReturn($julien);
        $userRepository->find(3)->willReturn($judith);

        $filterUsersToNotify->filter('family_42', [1, 2])->willReturn([1]);
        $filterUsersToNotify->filter('family_ABC', [1, 2, 3])->willReturn([1, 3]);

        $familyRepository->findOneByIdentifier('family_42')->willReturn($family42);
        $familyRepository->findOneByIdentifier('family_ABC')->willReturn($familyABC);

        $notifyUserAboutMissingMapping->forFamily(Argument::cetera())->shouldBeCalledTimes(3);
        $notifyUserAboutMissingMapping->forFamily($julia, $family42)->shouldBeCalled();
        $notifyUserAboutMissingMapping->forFamily($julia, $familyABC)->shouldBeCalled();
        $notifyUserAboutMissingMapping->forFamily($judith, $familyABC)->shouldBeCalled();

        $this->execute();
    }

    public function it_does_not_notify_if_user_id_does_not_correspond_to_an_existing_user(
        $userIdsAndFamilyCodesQuery,
        $userRepository,
        $familyRepository,
        $notifyUserAboutMissingMapping,
        $filterUsersToNotify,
        UserInterface $julia,
        Family $family42,
        Family $family43
    ): void {
        $userIdsAndFamilyCodesQuery->execute()->willReturn([
            'family_42' => [1],
            'family_43' => [2],
        ]);
        $filterUsersToNotify->filter('family_42', [1])->willReturn([1]);
        $filterUsersToNotify->filter('family_43', [2])->willReturn([2]);

        $userRepository->find(1)->willReturn($julia);
        $userRepository->find(2)->willReturn(null);

        $familyRepository->findOneByIdentifier('family_42')->willReturn($family42);
        $familyRepository->findOneByIdentifier('family_43')->willReturn($family43);

        $notifyUserAboutMissingMapping->forFamily(Argument::cetera())->shouldBeCalledTimes(1);
        $notifyUserAboutMissingMapping->forFamily($julia, $family42)->shouldBeCalled();

        $this->execute();
    }

    public function it_does_not_notify_if_family_id_does_not_correspond_to_an_existing_family(
        $userIdsAndFamilyCodesQuery,
        $userRepository,
        $familyRepository,
        $notifyUserAboutMissingMapping,
        UserInterface $julia
    ): void {
        $userIdsAndFamilyCodesQuery->execute()->willReturn(['family_42' => [1]]);

        $userRepository->find(1)->willReturn($julia);

        $familyRepository->findOneByIdentifier('family_42')->willReturn(null);

        $notifyUserAboutMissingMapping->forFamily(Argument::cetera())->shouldNotBeCalled();

        $this->execute();
    }
}
