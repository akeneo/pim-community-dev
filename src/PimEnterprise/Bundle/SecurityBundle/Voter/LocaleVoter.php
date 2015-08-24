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

use Oro\Bundle\UserBundle\Entity\Group;
use Pim\Bundle\CatalogBundle\Model\LocaleInterface;
use PimEnterprise\Bundle\SecurityBundle\Attributes;
use PimEnterprise\Bundle\SecurityBundle\Manager\LocaleAccessManager;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

/**
 * Locale voter, allows to know if a locale can be edited or consulted by a user depending on his groups
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 */
class LocaleVoter implements VoterInterface
{
    /**
     * @var LocaleAccessManager
     */
    protected $accessManager;

    /**
     * @param LocaleAccessManager $accessManager
     */
    public function __construct(LocaleAccessManager $accessManager)
    {
        $this->accessManager = $accessManager;
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
        return $class instanceof LocaleInterface;
    }

    /**
     * {@inheritdoc}
     */
    public function vote(TokenInterface $token, $locale, array $attributes)
    {
        $result = VoterInterface::ACCESS_ABSTAIN;
        if ($this->supportsClass($locale) && !is_string($token->getUser())) {
            foreach ($attributes as $attribute) {
                if ($this->supportsAttribute($attribute)) {
                    $result        = VoterInterface::ACCESS_DENIED;
                    $grantedGroups = $this->extractUserGroups($attribute, $locale);

                    foreach ($grantedGroups as $group) {
                        if ($token->getUser()->hasGroup($group)) {
                            return VoterInterface::ACCESS_GRANTED;
                        }
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Get usr groups for specific attribute and locale
     *
     * @param string          $attribute
     * @param LocaleInterface $locale
     *
     * @return Group[]
     */
    protected function extractUserGroups($attribute, $locale)
    {
        if ($attribute === Attributes::EDIT_ITEMS) {
            $grantedGroups = $this->accessManager->getEditUserGroups($locale);
        } else {
            $grantedGroups = $this->accessManager->getViewUserGroups($locale);
        }

        return $grantedGroups;
    }
}
