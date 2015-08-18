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

use PimEnterprise\Bundle\SecurityBundle\Attributes;
use PimEnterprise\Bundle\SecurityBundle\Entity\Repository\CategoryAccessRepository;
use PimEnterprise\Component\ProductAsset\Model\AssetInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Asset voter, allows to know if assets can be edited and/or consulted by a user depending on his user groups
 *
 * @author Marie Bochu <marie.bochu@akeneo.com>
 */
class AssetVoter implements VoterInterface
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
    public function supportsAttribute($attribute)
    {
        return in_array($attribute, [Attributes::VIEW, Attributes::EDIT]);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsClass($class)
    {
        return $class instanceof AssetInterface;
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

                    if ($this->isAssetAccessible($object, $token->getUser(), $attribute)) {
                        return VoterInterface::ACCESS_GRANTED;
                    }
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
            Attributes::EDIT => Attributes::EDIT_PRODUCTS,
            Attributes::VIEW => Attributes::VIEW_PRODUCTS,
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
}
