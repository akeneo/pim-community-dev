<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\Workflow\Bundle\Security;

use Akeneo\Pim\Permission\Component\Attributes as SecurityAttributes;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\EntityWithValuesDraftInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

/**
 * Voter of the product draft, determine if a user is the owner of the product draft.
 *
 * @author Julien Janvier <jjanvier@akeneo.com>
 */
class ProductDraftVoter extends Voter implements VoterInterface
{
    /**
     * {@inheritdoc}
     */
    public function vote(TokenInterface $token, $draft, array $attributes): int
    {
        if (!($draft instanceof EntityWithValuesDraftInterface)) {
            return self::ACCESS_ABSTAIN;
        }

        foreach ($attributes as $attribute) {
            if ($this->supports($attribute, $draft)) {
                if ($this->voteOnAttribute($attribute, $draft, $token)) {
                    return self::ACCESS_GRANTED;
                }

                return self::ACCESS_DENIED;
            }
        }

        return self::ACCESS_ABSTAIN;
    }

    /**
     * {@inheritdoc}
     */
    protected function supports($attribute, $subject)
    {
        return SecurityAttributes::OWN === $attribute && $subject instanceof EntityWithValuesDraftInterface;
    }

    /**
     * {@inheritdoc}
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        return $token->getUser()->getUserIdentifier() === $subject->getAuthor();
    }
}
