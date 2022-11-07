<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Permission\Bundle\Voter;

use Akeneo\Pim\Permission\Bundle\Entity\Repository\CategoryAccessRepository;
use Akeneo\Pim\Permission\Component\Attributes;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

/**
 * Category owner voter, indicates whether the user is the owner of products in at least one category
 *
 * @author Filips Alpe <filips@akeneo.com>
 */
class CategoryOwnerVoter extends Voter implements VoterInterface
{
    /**
     * @var CategoryAccessRepository
     */
    protected $accessRepository;

    /**
     * @param CategoryAccessRepository $accessRepository
     */
    public function __construct(CategoryAccessRepository $accessRepository)
    {
        $this->accessRepository = $accessRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function vote(TokenInterface $token, $object, array $attributes): int
    {
        foreach ($attributes as $attribute) {
            if (!$this->supports($attribute, $object)) {
                continue;
            }

            if ($this->voteOnAttribute($attribute, $object, $token)) {
                return VoterInterface::ACCESS_GRANTED;
            }

            return VoterInterface::ACCESS_DENIED;
        }

        return VoterInterface::ACCESS_ABSTAIN;
    }

    /**
     * {@inheritdoc}
     */
    protected function supports($attribute, $subject)
    {
        return $attribute === Attributes::OWN_AT_LEAST_ONE_CATEGORY;
    }

    /**
     * {@inheritdoc}
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();
        if (null === $user || !is_object($user)) {
            return false;
        }

        return $this->accessRepository->isOwner($user);
    }
}
