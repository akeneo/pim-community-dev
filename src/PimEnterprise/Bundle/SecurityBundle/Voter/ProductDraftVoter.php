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

use Pim\Bundle\CatalogBundle\Repository\AttributeGroupRepositoryInterface;
use Pim\Component\Catalog\Model\AttributeGroupInterface;
use PimEnterprise\Bundle\SecurityBundle\Attributes as SecurityAttributes;
use PimEnterprise\Bundle\SecurityBundle\Manager\AttributeGroupAccessManager;
use PimEnterprise\Bundle\WorkflowBundle\Model\ProductDraftInterface;
use PimEnterprise\Bundle\WorkflowBundle\Security\Attributes as WorkflowAttributes;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Voter of the product draft, determine if :
 *  - a user is the owner of the product draft
 *  - a user can fully review a proposal
 *  - a user can partially review a proposal
 *
 * @author Julien Janvier <jjanvier@akeneo.com>
 */
class ProductDraftVoter implements VoterInterface
{
    /** @var AttributeGroupRepositoryInterface */
    protected $attrGroupRepository;

    /** @var AttributeGroupAccessManager */
    protected $attrGroupAccessMgr;

    /**
     * @param AttributeGroupRepositoryInterface $attrGroupRepository
     * @param AttributeGroupAccessManager       $attrGroupAccessMgr
     */
    public function __construct(
        AttributeGroupRepositoryInterface $attrGroupRepository,
        AttributeGroupAccessManager $attrGroupAccessMgr
    ) {
        $this->attrGroupRepository = $attrGroupRepository;
        $this->attrGroupAccessMgr  = $attrGroupAccessMgr;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsAttribute($attribute)
    {
        return in_array(
            $attribute,
            [
                WorkflowAttributes::FULL_REVIEW,
                WorkflowAttributes::PARTIAL_REVIEW,
                SecurityAttributes::OWN
            ],
            true
        );
    }

    /**
     * {@inheritdoc}
     */
    public function supportsClass($class)
    {
        return $class instanceof ProductDraftInterface;
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
                switch ($attribute) {
                    case WorkflowAttributes::FULL_REVIEW:
                        $userGranted = $this->canFullyReview($token->getUser(), $object);
                        break;
                    case WorkflowAttributes::PARTIAL_REVIEW:
                        $userGranted = $this->canPartiallyReview($token->getUser(), $object);
                        break;
                    case SecurityAttributes::OWN:
                        $userGranted = $this->isOwner($token->getUser(), $object);
                        break;
                    default:
                        return VoterInterface::ACCESS_ABSTAIN;
                }

                return $userGranted ? VoterInterface::ACCESS_GRANTED : VoterInterface::ACCESS_DENIED;
            }
        }

        return VoterInterface::ACCESS_ABSTAIN;
    }

    /**
     * @param UserInterface         $user
     * @param ProductDraftInterface $draft
     *
     * @return bool
     */
    protected function isOwner(UserInterface $user, ProductDraftInterface $draft)
    {
        return $user->getUsername() === $draft->getAuthor();
    }

    /**
     * A user can fully review a draft only if he/she can edit all values contained in it.
     *
     * @param UserInterface         $user
     * @param ProductDraftInterface $draft
     *
     * @return bool
     */
    protected function canFullyReview(UserInterface $user, ProductDraftInterface $draft)
    {
        foreach ($this->getAttributeGroupsImpactedByADraft($draft) as $group) {
            if (!$this->attrGroupAccessMgr->isUserGranted($user, $group, SecurityAttributes::EDIT_ATTRIBUTES)) {
                return false;
            }
        }

        return true;
    }

    /**
     * A user can partially review a draft if he/she can edit at least one value contained in it.
     *
     * @param UserInterface         $user
     * @param ProductDraftInterface $draft
     *
     * @return bool
     */
    protected function canPartiallyReview(UserInterface $user, ProductDraftInterface $draft)
    {
        foreach ($this->getAttributeGroupsImpactedByADraft($draft) as $group) {
            if ($this->attrGroupAccessMgr->isUserGranted($user, $group, SecurityAttributes::EDIT_ATTRIBUTES)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param ProductDraftInterface $draft
     *
     * @return AttributeGroupInterface
     */
    protected function getAttributeGroupsImpactedByADraft(ProductDraftInterface $draft)
    {
        $changes = $draft->getChanges();
        if (!isset($changes['values'])) {
            return [];
        }

        $attributeCodes = array_keys($changes['values']);

        return $this->attrGroupRepository->getAttributeGroupsFromAttributeCodes($attributeCodes);
    }
}
