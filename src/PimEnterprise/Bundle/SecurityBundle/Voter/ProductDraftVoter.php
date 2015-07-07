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

use Pim\Bundle\CatalogBundle\Model\AttributeGroupInterface;
use Pim\Bundle\CatalogBundle\Repository\AttributeGroupRepositoryInterface;
use PimEnterprise\Bundle\SecurityBundle\Attributes;
use PimEnterprise\Bundle\SecurityBundle\Manager\AttributeGroupAccessManager;
use PimEnterprise\Bundle\WorkflowBundle\Model\ProductDraft;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Voter of the product draft, determine if,
 *  - a user is the owner of the product draft
 *  - a user can approve or refuse a proposal (ie: if he can edit all values contained in this draft).
 *
 * @author Julien Janvier <jjanvier@akeneo.com>
 */
class ProductDraftVoter implements VoterInterface
{
    /** @var AttributeGroupRepositoryInterface */
    protected $attrGroupRepository;

    /** @var AttributeGroupAccessManager */
    protected $attrGroupAccessManager;

    /**
     * @param AttributeGroupRepositoryInterface $attrGroupRepository
     * @param AttributeGroupAccessManager       $attrGroupAccessManager
     */
    public function __construct(
        AttributeGroupRepositoryInterface $attrGroupRepository,
        AttributeGroupAccessManager $attrGroupAccessManager
    ) {
        $this->attrGroupRepository = $attrGroupRepository;
        $this->attrGroupAccessManager = $attrGroupAccessManager;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsAttribute($attribute)
    {
        return (Attributes::EDIT_ATTRIBUTES === $attribute) || (Attributes::OWN === $attribute);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsClass($class)
    {
        return $class instanceof ProductDraft;
    }

    /**
     * {@inheritdoc}
     */
    public function vote(TokenInterface $token, $object, array $attributes)
    {
        if (!$this->supportsClass($object)) {
            return VoterInterface::ACCESS_ABSTAIN;
        }

        foreach ($attributes as $attribute) {
            if ($this->supportsAttribute($attribute)) {
                if (Attributes::EDIT_ATTRIBUTES === $attribute) {
                    return $this->canApprove($token->getUser(), $object) ?
                        VoterInterface::ACCESS_GRANTED :
                        VoterInterface::ACCESS_DENIED;
                } elseif (Attributes::OWN === $attribute) {
                    return $this->isOwner($token->getUser(), $object) ?
                        VoterInterface::ACCESS_GRANTED :
                        VoterInterface::ACCESS_DENIED;
                }
            }
        }

        return VoterInterface::ACCESS_ABSTAIN;
    }

    /**
     * @param UserInterface $user
     * @param ProductDraft  $draft
     *
     * @return bool
     */
    protected function isOwner(UserInterface $user, ProductDraft $draft)
    {
        return $user->getUsername() === $draft->getAuthor();
    }

    /**
     * @param UserInterface $user
     * @param ProductDraft  $draft
     *
     * @return bool
     */
    protected function canApprove(UserInterface $user, ProductDraft $draft)
    {
        foreach ($this->getAttributeGroupsImpactedByADraft($draft) as $group) {
            $userGranted = $this->attrGroupAccessManager->isUserGranted($user, $group, Attributes::EDIT_ATTRIBUTES);
            if (false === $userGranted) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param ProductDraft $draft
     *
     * @return AttributeGroupInterface
     */
    protected function getAttributeGroupsImpactedByADraft(ProductDraft $draft)
    {
        $changes = $draft->getChanges();
        if (!isset($changes['values'])) {
            return [];
        }

        $changes = $changes['values'];
        $attributeCodes = [];

        foreach ($changes as $change) {
            $attributeCodes[] = $change['__context__']['attribute'];
        }

        return $this->attrGroupRepository->getAttributeGroupsFromAttributeCodes($attributeCodes);
    }
}
