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

use Pim\Component\Catalog\Model\LocaleInterface;
use Pim\Component\User\Model\GroupInterface;
use PimEnterprise\Bundle\SecurityBundle\Manager\LocaleAccessManager;
use PimEnterprise\Component\Security\Attributes;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

/**
 * Locale voter, allows to know if a locale can be edited or consulted by a user depending on his groups
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 */
class LocaleVoter extends Voter implements VoterInterface
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
    public function vote(TokenInterface $token, $locale, array $attributes)
    {
        $result = VoterInterface::ACCESS_ABSTAIN;

        if (!$locale instanceof LocaleInterface || is_string($token->getUser())) {
            return $result;
        }

        foreach ($attributes as $attribute) {
            if ($this->supports($attribute, $locale)) {
                $result = VoterInterface::ACCESS_DENIED;

                if ($this->voteOnAttribute($attribute, $locale, $token)) {
                    return VoterInterface::ACCESS_GRANTED;
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
     * @return GroupInterface[]
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

    /**
     * {@inheritdoc}
     */
    protected function supports($attribute, $subject)
    {
        return in_array($attribute, [Attributes::VIEW_ITEMS, Attributes::EDIT_ITEMS]) &&
            $subject instanceof LocaleInterface;
    }

    /**
     * {@inheritdoc}
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $grantedGroups = $this->extractUserGroups($attribute, $subject);

        foreach ($grantedGroups as $group) {
            if ($token->getUser()->hasGroup($group)) {
                return true;
            }
        }

        return false;
    }
}
