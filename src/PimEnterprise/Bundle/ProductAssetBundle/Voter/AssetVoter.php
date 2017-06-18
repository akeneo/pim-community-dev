<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\ProductAssetBundle\Voter;

use PimEnterprise\Bundle\SecurityBundle\Entity\Repository\CategoryAccessRepository;
use PimEnterprise\Component\ProductAsset\Model\AssetInterface;
use PimEnterprise\Component\Security\Attributes;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Asset voter, allows to know if assets can be edited and/or consulted by a user depending on his user groups
 *
 * @author Marie Bochu <marie.bochu@akeneo.com>
 */
class AssetVoter extends Voter implements VoterInterface
{
    /** @var CategoryAccessRepository */
    protected $categoryAccessRepo;

    /**
     * @param CategoryAccessRepository $categoryAccessRepo
     */
    public function __construct(CategoryAccessRepository $categoryAccessRepo)
    {
        $this->categoryAccessRepo = $categoryAccessRepo;
    }

    /**
     * {@inheritdoc}
     */
    public function vote(TokenInterface $token, $object, array $attributes)
    {
        $result = VoterInterface::ACCESS_ABSTAIN;

        if (!$object instanceof AssetInterface) {
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
     * Determines if an asset is accessible for the user,
     * - no categories : the asset is accessible
     * - categories : we apply category's permissions
     *
     * @param AssetInterface $asset     the asset
     * @param UserInterface  $user      the user
     * @param string         $attribute the attribute
     *
     * @return bool
     */
    protected function isAssetAccessible(AssetInterface $asset, UserInterface $user, $attribute)
    {
        if (0 === count($asset->getCategories())) {
            return VoterInterface::ACCESS_GRANTED;
        }

        $assetToCategory = [
            Attributes::EDIT => Attributes::EDIT_ITEMS,
            Attributes::VIEW => Attributes::VIEW_ITEMS,
        ];
        if (!isset($assetToCategory[$attribute])) {
            return false;
        }
        $categoryAttribute = $assetToCategory[$attribute];

        $categoryIds = [];
        foreach ($asset->getCategories() as $category) {
            $categoryIds[] = $category->getId();
        }

        return $this->categoryAccessRepo->isCategoriesGranted($user, $categoryAttribute, $categoryIds);
    }

    /**
     * {@inheritdoc}
     */
    protected function supports($attribute, $subject)
    {
        return in_array($attribute, [Attributes::VIEW, Attributes::EDIT]) && $subject instanceof AssetInterface;
    }

    /**
     * {@inheritdoc}
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        return $this->isAssetAccessible($subject, $token->getUser(), $attribute);
    }
}
