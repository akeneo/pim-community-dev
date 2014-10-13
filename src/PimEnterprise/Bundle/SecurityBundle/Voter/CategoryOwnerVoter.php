<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\SecurityBundle\Voter;

use PimEnterprise\Bundle\SecurityBundle\Attributes;
use PimEnterprise\Bundle\SecurityBundle\Entity\Repository\CategoryAccessRepository;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

/**
 * Category owner voter, indicates whether the user is the owner of products in at least one category
 *
 * @author Filips Alpe <filips@akeneo.com>
 */
class CategoryOwnerVoter implements VoterInterface
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
    public function supportsAttribute($attribute)
    {
        return $attribute === Attributes::OWN_AT_LEAST_ONE_CATEGORY;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsClass($class)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function vote(TokenInterface $token, $object, array $attributes)
    {
        foreach ($attributes as $attribute) {
            if (!$this->supportsAttribute($attribute)) {
                continue;
            }

            $user = $token->getUser();
            if (null === $user || !is_object($user)) {
                return VoterInterface::ACCESS_DENIED;
            }

            return $this->accessRepository->isOwner($user) ?
                VoterInterface::ACCESS_GRANTED :
                VoterInterface::ACCESS_DENIED;
        }

        return VoterInterface::ACCESS_ABSTAIN;
    }
}
