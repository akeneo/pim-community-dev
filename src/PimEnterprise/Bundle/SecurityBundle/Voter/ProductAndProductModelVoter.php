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

use Akeneo\Component\Classification\CategoryAwareInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductModelInterface;
use PimEnterprise\Bundle\SecurityBundle\Entity\Repository\CategoryAccessRepository;
use PimEnterprise\Component\Security\Attributes;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Product and product model voter, allows to know if products can be published, reviewed, edited, consulted by a
 * user depending on his user groups
 *
 * @author Julien Janvier <julien.janvier@akeneo.com>
 */
class ProductAndProductModelVoter extends Voter implements VoterInterface
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
     * Determines if a category aware entity is accessible for the user,
     * - no categories : the product is accessible
     * - categories : we apply category's permissions
     *
     * @param CategoryAwareInterface $categoryAwareEntity
     * @param UserInterface          $user the user
     * @param string                 $attribute the attribute
     *
     * @return bool
     */
    protected function isCategoryAwareEntityAccessible(
        CategoryAwareInterface $categoryAwareEntity,
        UserInterface $user,
        $attribute
    ) {
        if (count($categoryAwareEntity->getCategories()) === 0) {
            return VoterInterface::ACCESS_GRANTED;
        }

        $productToCategory = [
            Attributes::OWN  => Attributes::OWN_PRODUCTS,
            Attributes::EDIT => Attributes::EDIT_ITEMS,
            Attributes::VIEW => Attributes::VIEW_ITEMS,
        ];
        if (!isset($productToCategory[$attribute])) {
            return false;
        }
        $categoryAttribute = $productToCategory[$attribute];

        $categoryIds = [];
        foreach ($categoryAwareEntity->getCategories() as $category) {
            $categoryIds[] = $category->getId();
        }

        return $this->categoryAccessRepo->isCategoriesGranted($user, $categoryAttribute, $categoryIds);
    }

    /**
     * {@inheritdoc}
     */
    protected function supports($attribute, $subject)
    {
        return in_array($attribute, [Attributes::VIEW, Attributes::EDIT, Attributes::OWN]) &&
            ($subject instanceof ProductInterface || $subject instanceof ProductModelInterface);
    }

    /**
     * {@inheritdoc}
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        return $this->isCategoryAwareEntityAccessible($subject, $token->getUser(), $attribute);
    }
}
