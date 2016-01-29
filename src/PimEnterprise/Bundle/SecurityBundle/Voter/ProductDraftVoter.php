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

use Pim\Bundle\CatalogBundle\Repository\AttributeGroupRepositoryInterface;
use Pim\Component\Catalog\Model\AttributeGroupInterface;
use Pim\Component\Catalog\Model\LocaleInterface;
use Pim\Component\Catalog\Repository\LocaleRepositoryInterface;
use PimEnterprise\Bundle\SecurityBundle\Attributes as SecurityAttributes;
use PimEnterprise\Bundle\SecurityBundle\Manager\AttributeGroupAccessManager;
use PimEnterprise\Bundle\WorkflowBundle\Model\ProductDraftInterface;
use PimEnterprise\Bundle\WorkflowBundle\Security\Attributes as WorkflowAttributes;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Voter of the product draft, determine if :
 *  - a user is the owner of the product draft
 *  - a user can fully review a proposal
 *  - a user can partially review a proposal
 *
 * @author Julien Janvier <jjanvier@akeneo.com>
 */
class ProductDraftVoter implements VoterInterface
{
    /** @var AttributeGroupRepositoryInterface */
    protected $attrGroupRepository;

    /** @var LocaleRepositoryInterface */
    protected $localeRepository;

    /** @var AttributeGroupAccessManager */
    protected $attrGroupAccessMgr;

    /** @var VoterInterface */
    protected $localeVoter;

    /**
     * @param AttributeGroupRepositoryInterface $attrGroupRepository
     * @param LocaleRepositoryInterface         $localeRepository
     * @param AttributeGroupAccessManager       $attrGroupAccessMgr
     * @param VoterInterface                    $localeVoter
     */
    public function __construct(
        AttributeGroupRepositoryInterface $attrGroupRepository,
        LocaleRepositoryInterface $localeRepository,
        AttributeGroupAccessManager $attrGroupAccessMgr,
        VoterInterface $localeVoter
    ) {
        $this->attrGroupRepository = $attrGroupRepository;
        $this->localeRepository    = $localeRepository;
        $this->attrGroupAccessMgr  = $attrGroupAccessMgr;
        $this->localeVoter         = $localeVoter;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsAttribute($attribute)
    {
        return in_array(
            $attribute,
            [
                WorkflowAttributes::FULL_REVIEW,
                WorkflowAttributes::PARTIAL_REVIEW,
                SecurityAttributes::OWN
            ],
            true
        );
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
    public function vote(TokenInterface $token, $object, array $attributes)
    {
        if (!$this->supportsClass($object)) {
            return self::ACCESS_ABSTAIN;
        }

        foreach ($attributes as $attribute) {
            if ($this->supportsAttribute($attribute)) {
                switch ($attribute) {
                    case WorkflowAttributes::FULL_REVIEW:
                        $userGranted = $this->canFullyReview($token, $object);
                        break;
                    case WorkflowAttributes::PARTIAL_REVIEW:
                        $userGranted = $this->canPartiallyReview($token, $object);
                        break;
                    case SecurityAttributes::OWN:
                        $userGranted = $this->isOwner($token, $object);
                        break;
                    default:
                        return self::ACCESS_ABSTAIN;
                }

                return $userGranted ? self::ACCESS_GRANTED : self::ACCESS_DENIED;
            }
        }

        return self::ACCESS_ABSTAIN;
    }

    /**
     * @param TokenInterface        $token
     * @param ProductDraftInterface $draft
     *
     * @return bool
     */
    protected function isOwner(TokenInterface $token, ProductDraftInterface $draft)
    {
        return $token->getUser()->getUsername() === $draft->getAuthor();
    }

    /**
     * A user can fully review a draft only if he/she can edit all values contained in it.
     *
     * @param TokenInterface        $token
     * @param ProductDraftInterface $draft
     *
     * @return bool
     */
    protected function canFullyReview(TokenInterface $token, ProductDraftInterface $draft)
    {
        $changes = $draft->getChangesToReview();
        if (!isset($changes['values'])) {
            return false;
        }

        foreach ($changes['values'] as $attributeCode => $attributeChanges) {
            $group = $this->getGroupByAttributeCode($attributeCode);
            if (!$this->isGroupGranted($token, $group)) {
                return false;
            }

            foreach ($attributeChanges as $attributeChange) {
                if (null !== $attributeChange['locale']) {
                    $locale = $this->getLocaleByCode($attributeChange['locale']);
                    if (!$this->isLocaleGranted($token, $locale)) {
                        return false;
                    }
                }
            }
        }

        return true;
    }

    /**
     * A user can partially review a draft if he/she can edit at least one value contained in it.
     *
     * @param TokenInterface        $token
     * @param ProductDraftInterface $draft
     *
     * @return bool
     */
    protected function canPartiallyReview(TokenInterface $token, ProductDraftInterface $draft)
    {
        $changes = $draft->getChangesToReview();
        if (!isset($changes['values'])) {
            return false;
        }

        foreach ($changes['values'] as $attributeCode => $attributeChanges) {
            $group = $this->getGroupByAttributeCode($attributeCode);
            $isGroupGranted = $this->isGroupGranted($token, $group);

            foreach ($attributeChanges as $attributeChange) {
                if (null !== $attributeChange['locale']) {
                    $locale = $this->getLocaleByCode($attributeChange['locale']);
                    if ($isGroupGranted && $this->isLocaleGranted($token, $locale)) {
                        return true;
                    }
                } elseif ($isGroupGranted) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param TokenInterface          $token
     * @param AttributeGroupInterface $group
     *
     * @return bool
     */
    protected function isGroupGranted(TokenInterface $token, AttributeGroupInterface $group)
    {
        return $this->attrGroupAccessMgr->isUserGranted($token->getUser(), $group, SecurityAttributes::EDIT_ATTRIBUTES);
    }

    /**
     * @param TokenInterface  $token
     * @param LocaleInterface $locale
     *
     * @return bool
     */
    protected function isLocaleGranted(TokenInterface $token, LocaleInterface $locale)
    {
        return self::ACCESS_GRANTED === $this->localeVoter->vote($token, $locale, [SecurityAttributes::EDIT_ITEMS]);
    }

    /**
     * @param $attributeCode
     *
     * @return AttributeGroupInterface
     */
    protected function getGroupByAttributeCode($attributeCode)
    {
        return $this->attrGroupRepository->getAttributeGroupsFromAttributeCodes([$attributeCode])[0];
    }

    /**
     * @param string $localeCode
     *
     * @return LocaleInterface
     */
    protected function getLocaleByCode($localeCode)
    {
        return $this->localeRepository->findOneByIdentifier($localeCode);
    }
}
