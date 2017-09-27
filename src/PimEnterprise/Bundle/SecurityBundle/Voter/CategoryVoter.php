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

use PimEnterprise\Bundle\SecurityBundle\Manager\CategoryAccessManager;
use PimEnterprise\Component\Security\Attributes;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

/**
 * Category voter, allows to know if products of a category can be edited or consulted by a
 * user depending on his user groups
 *
 * @author Julien Janvier <julien.janvier@akeneo.com>
 */
class CategoryVoter extends Voter implements VoterInterface
{
    /** @var CategoryAccessManager */
    protected $accessManager;

    /** @var string */
    protected $className;

    /**
     * @param CategoryAccessManager $accessManager
     * @param                       $className
     */
    public function __construct(CategoryAccessManager $accessManager, $className)
    {
        $this->accessManager = $accessManager;
        $this->className = $className;
    }

    /**
     * {@inheritdoc}
     */
    public function vote(TokenInterface $token, $object, array $attributes)
    {
        $result = VoterInterface::ACCESS_ABSTAIN;

        if (!$object instanceof $this->className) {
            return $result;
        }

        foreach ($attributes as $attribute) {
            if ($this->supports($attribute, $object)) {
                $result = VoterInterface::ACCESS_DENIED;

                if ($this->voteOnAttribute($attribute, $object, $token)) {
                    return VoterInterface::ACCESS_GRANTED;
                }
            }
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    protected function supports($attribute, $subject)
    {
        return in_array($attribute, [Attributes::VIEW_ITEMS, Attributes::EDIT_ITEMS, Attributes::OWN_PRODUCTS]) &&
            $subject instanceof $this->className;
    }

    /**
     * {@inheritdoc}
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        return $this->accessManager->isUserGranted($token->getUser(), $subject, $attribute);
    }
}
