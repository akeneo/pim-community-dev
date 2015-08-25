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
use PimEnterprise\Bundle\SecurityBundle\Manager\CategoryAccessManager;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

/**
 * Category voter, allows to know if products of a category can be edited or consulted by a
 * user depending on his user groups
 *
 * @author Julien Janvier <julien.janvier@akeneo.com>
 */
class CategoryVoter implements VoterInterface
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
        $this->className     = $className;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsAttribute($attribute)
    {
        return in_array($attribute, [Attributes::VIEW_ITEMS, Attributes::EDIT_ITEMS]);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsClass($class)
    {
        return $class instanceof $this->className;
    }

    /**
     * {@inheritdoc}
     */
    public function vote(TokenInterface $token, $object, array $attributes)
    {
        $result = VoterInterface::ACCESS_ABSTAIN;

        if (!$this->supportsClass($object)) {
            return $result;
        }

        foreach ($attributes as $attribute) {
            if ($this->supportsAttribute($attribute)) {
                $result = VoterInterface::ACCESS_DENIED;

                if ($this->accessManager->isUserGranted($token->getUser(), $object, $attribute)) {
                    return VoterInterface::ACCESS_GRANTED;
                }
            }
        }

        return $result;
    }
}
