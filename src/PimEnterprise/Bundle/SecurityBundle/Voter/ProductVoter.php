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

use Pim\Component\Catalog\Model\ProductInterface;
use PimEnterprise\Bundle\SecurityBundle\Entity\Repository\CategoryAccessRepository;
use PimEnterprise\Component\Security\Attributes;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Product voter, allows to know if products can be published, reviewed, edited, consulted by a
 * user depending on his user groups
 *
 * @author Julien Janvier <julien.janvier@akeneo.com>
 */
class ProductVoter extends Voter implements VoterInterface
{
    /**
     * @var CategoryAccessRepository
     */
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

        if ($object instanceof ProductInterface) {
            foreach ($attributes as $attribute) {
                if ($this->supports($attribute, $object)) {
                    $result = VoterInterface::ACCESS_DENIED;

                    if ($this->voteOnAttribute($attribute, $object, $token)) {
                        return VoterInterface::ACCESS_GRANTED;
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Determines if a product is accessible for the user,
     * - no categories : the product is accessible
     * - categories : we apply category's permissions
     *
     * @param ProductInterface $product   the product
     * @param UserInterface    $user      the user
     * @param string           $attribute the attribute
     *
     * @return bool
     */
    protected function isProductAccessible(ProductInterface $product, UserInterface $user, $attribute)
    {
        if (count($product->getCategories()) === 0) {
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
        foreach ($product->getCategories() as $category) {
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
            $subject instanceof ProductInterface;
    }

    /**
     * {@inheritdoc}
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        return $this->isProductAccessible($subject, $token->getUser(), $attribute);
    }
}
