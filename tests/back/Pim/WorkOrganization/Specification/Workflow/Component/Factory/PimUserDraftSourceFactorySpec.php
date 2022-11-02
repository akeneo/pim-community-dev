<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Pim\WorkOrganization\Workflow\Component\Factory;

use Akeneo\Pim\WorkOrganization\Workflow\Component\Factory\PimUserDraftSourceFactory;
use Akeneo\UserManagement\Component\Model\UserInterface;
use PhpSpec\ObjectBehavior;

/**
 * @author Olivier Pontier <olivier.pontier@akeneo.com>
 */
class PimUserDraftSourceFactorySpec extends ObjectBehavior
{
    public function it_creates_a_draft_source_from_a_pim_user(
        UserInterface $user
    )
    {
        $username = 'mary';
        $userFullName = 'Mary Smith';

        $user->getUserIdentifier()->willReturn($username);
        $user->getFullName()->willReturn($userFullName);

        $draftSource = $this->createFromUser($user);

        $draftSource->getAuthor()->shouldBe($username);
        $draftSource->getAuthorLabel()->shouldBe($userFullName);
        $draftSource->getSource()->shouldBe(PimUserDraftSourceFactory::PIM_SOURCE_CODE);
        $draftSource->getSourceLabel()->shouldBe(PimUserDraftSourceFactory::PIM_SOURCE_LABEL);
    }
}
