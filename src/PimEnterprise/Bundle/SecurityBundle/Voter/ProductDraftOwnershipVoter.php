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

use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use PimEnterprise\Bundle\SecurityBundle\Attributes;
use PimEnterprise\Bundle\WorkflowBundle\Model\ProductDraft;

/**
 * Voter of the product draft ownership attribute
 *
 * TODO 1.3: merge this class with ProductDraftApprovalVoter to have only one voter and rename this voter
 * ProductDraftVoter
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 */
class ProductDraftOwnershipVoter implements VoterInterface
{
    /**
     * {@inheritdoc}
     */
    public function supportsAttribute($attribute)
    {
        return Attributes::OWN === $attribute;
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
                if ($token->getUser()->getUsername() === $object->getAuthor()) {
                    return VoterInterface::ACCESS_GRANTED;
                }

                return VoterInterface::ACCESS_DENIED;
            }
        }

        return VoterInterface::ACCESS_ABSTAIN;
    }
}
