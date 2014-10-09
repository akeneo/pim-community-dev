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

use PimEnterprise\Bundle\SecurityBundle\Entity\Repository\CategoryAccessRepository;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

/**
 * Product owner voter, indicates whether the user is the owner of any products
 *
 * @author Filips Alpe <filips@akeneo.com>
 */
class ProductOwnerVoter implements VoterInterface
{
    /** @staticvar string */
    const OWN = 'pimee_security_own_products';

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
        return $attribute === static::OWN;
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
