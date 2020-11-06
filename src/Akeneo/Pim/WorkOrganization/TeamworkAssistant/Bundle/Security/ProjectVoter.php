<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\TeamworkAssistant\Bundle\Security;

use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Model\ProjectInterface;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Repository\UserRepositoryInterface;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Webmozart\Assert\Assert;

/**
 * Project voter, allow to know if a user has own and/or contribute access to a project.
 *
 * @author Arnaud Langlade <arnaud.langlade@akeneo.com>
 */
class ProjectVoter extends Voter implements VoterInterface
{
    const OWN = 'OWN';
    const CONTRIBUTE = 'CONTRIBUTE';

    /** @var UserRepositoryInterface */
    protected $userRepository;

    /**
     * @param UserRepositoryInterface $userRepository
     */
    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * {@inheritdoc}
     */
    protected function supports($attribute, $subject)
    {
        return in_array($attribute, [self::OWN, self::CONTRIBUTE]) && $subject instanceof ProjectInterface;
    }

    /**
     * {@inheritdoc}
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();

        if (null === $user) {
            return false;
        }

        Assert::implementsInterface($user, UserInterface::class);
        switch ($attribute) {
            case self::OWN:
                return $subject->getOwner()->getId() === $user->getId();
            case self::CONTRIBUTE:
                return $this->userRepository->isProjectContributor($subject, $user);
        }

        return false;
    }
}
