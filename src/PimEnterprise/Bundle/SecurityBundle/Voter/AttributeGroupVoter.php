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
use PimEnterprise\Bundle\SecurityBundle\Attributes;
use PimEnterprise\Bundle\SecurityBundle\Manager\AttributeGroupAccessManager;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

/**
 * Attribute group voter, allows to know if attributes of a group can be edited or consulted by a
 * user depending on his user groups
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 */
class AttributeGroupVoter implements VoterInterface
{
    /**
     * @var AttributeGroupAccessManager
     */
    protected $accessManager;

    /**
     * @param AttributeGroupAccessManager $accessManager
     */
    public function __construct(AttributeGroupAccessManager $accessManager)
    {
        $this->accessManager = $accessManager;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsAttribute($attribute)
    {
        return in_array($attribute, [Attributes::VIEW_ATTRIBUTES, Attributes::EDIT_ATTRIBUTES]);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsClass($class)
    {
        return $class instanceof AttributeGroupInterface;
    }

    /**
     * {@inheritdoc}
     */
    public function vote(TokenInterface $token, $object, array $attributes)
    {
        $result = VoterInterface::ACCESS_ABSTAIN;

        if ($this->supportsClass($object)) {
            foreach ($attributes as $attribute) {
                if ($this->supportsAttribute($attribute)) {
                    $result = VoterInterface::ACCESS_DENIED;

                    if ($this->accessManager->isUserGranted($token->getUser(), $object, $attribute)) {
                        return VoterInterface::ACCESS_GRANTED;
                    }
                }
            }
        }

        return $result;
    }
}
