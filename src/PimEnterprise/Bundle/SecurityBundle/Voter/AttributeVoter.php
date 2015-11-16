<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\SecurityBundle\Voter;

use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use PimEnterprise\Bundle\SecurityBundle\Attributes;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

/**
 * Attribute voter, allows to know if attribute can be edited or consulted by a user depending on his groups
 *
 * @author Clement Gautier <clement.gautier@akeneo.com>
 */
class AttributeVoter implements VoterInterface
{
    /** @var AttributeGroupVoter */
    protected $attributeGroupVoter;

    /**
     * @param AttributeGroupVoter $attributeGroupVoter
     */
    public function __construct(AttributeGroupVoter $attributeGroupVoter)
    {
        $this->attributeGroupVoter = $attributeGroupVoter;
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
        return $class instanceof AttributeInterface;
    }

    /**
     * {@inheritdoc}
     */
    public function vote(TokenInterface $token, $object, array $attributes)
    {
        if (!$this->supportsClass($object)) {
            return VoterInterface::ACCESS_ABSTAIN;
        }

        return $this->attributeGroupVoter->vote($token, $object->getgroup(), $attributes);
    }
}
