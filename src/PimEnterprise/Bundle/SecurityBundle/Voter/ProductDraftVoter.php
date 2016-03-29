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

use PimEnterprise\Component\Security\Attributes as SecurityAttributes;
use PimEnterprise\Component\Workflow\Model\ProductDraftInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

/**
 * Voter of the product draft, determine if a user is the owner of the product draft.
 *
 * @author Julien Janvier <jjanvier@akeneo.com>
 */
class ProductDraftVoter implements VoterInterface
{
    /**
     * {@inheritdoc}
     */
    public function supportsAttribute($attribute)
    {
        return SecurityAttributes::OWN === $attribute;
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
    public function vote(TokenInterface $token, $draft, array $attributes)
    {
        if (!$this->supportsClass($draft)) {
            return self::ACCESS_ABSTAIN;
        }

        $userGranted = $token->getUser()->getUsername() === $draft->getAuthor();

        foreach ($attributes as $attribute) {
            if ($this->supportsAttribute($attribute)) {
                return $userGranted ? self::ACCESS_GRANTED : self::ACCESS_DENIED;
            }
        }

        return self::ACCESS_ABSTAIN;
    }
}
