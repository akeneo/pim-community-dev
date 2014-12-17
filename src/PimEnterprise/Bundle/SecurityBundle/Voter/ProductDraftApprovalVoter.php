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

use Pim\Bundle\CatalogBundle\Entity\Repository\AttributeGroupRepository;
use PimEnterprise\Bundle\SecurityBundle\Attributes;
use PimEnterprise\Bundle\SecurityBundle\Manager\AttributeGroupAccessManager;
use PimEnterprise\Bundle\WorkflowBundle\Model\ProductDraft;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Voter of the product draft approval attribute. Determine if a user can approve or refuse a proposal (ie: if he
 * can edit all values contained in this draft).
 *
 * TODO 1.3: merge this class with ProductDraftOwnershipVoter to have only one voter
 *
 * @author Julien Janvier <jjanvier@akeneo.com>
 */
class ProductDraftApprovalVoter implements VoterInterface
{
    /** @var AttributeGroupRepository */
    protected $attrGroupRepository;

    /** @var AttributeGroupAccessManager */
    protected $attrGroupAccessManager;

    /**
     * @param AttributeGroupRepository    $attrGroupRepository
     * @param AttributeGroupAccessManager $attrGroupAccessManager
     */
    public function __construct(
        AttributeGroupRepository $attrGroupRepository,
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
        return Attributes::EDIT_ATTRIBUTES === $attribute;
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
                return $this->canUserApproveDraft($token->getUser(), $object) ?
                    VoterInterface::ACCESS_GRANTED :
                    VoterInterface::ACCESS_DENIED
                ;
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
    protected function canUserApproveDraft(UserInterface $user, ProductDraft $draft)
    {
        foreach ($this->getAttributeGroupsImpactedByADraft($draft) as $group) {
            if (false === $this->attrGroupAccessManager->isUserGranted(
                    $user,
                    $group,
                    Attributes::EDIT_ATTRIBUTES)
            ) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param ProductDraft $draft
     *
     * @return \Pim\Bundle\CatalogBundle\Entity\AttributeGroup[]
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
