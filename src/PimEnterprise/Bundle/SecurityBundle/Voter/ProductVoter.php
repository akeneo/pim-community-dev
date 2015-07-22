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

use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use PimEnterprise\Bundle\SecurityBundle\Attributes;
use PimEnterprise\Bundle\SecurityBundle\Entity\Repository\CategoryAccessRepository;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Product voter, allows to know if products can be published, reviewed, edited, consulted by a
 * user depending on his user groups
 *
 * @author Julien Janvier <julien.janvier@akeneo.com>
 */
class ProductVoter implements VoterInterface
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
    public function supportsAttribute($attribute)
    {
        return in_array($attribute, [Attributes::VIEW, Attributes::EDIT, Attributes::OWN]);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsClass($class)
    {
        return $class instanceof ProductInterface;
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

                    if ($this->isProductAccessible($object, $token->getUser(), $attribute)) {
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
            Attributes::EDIT => Attributes::EDIT_PRODUCTS,
            Attributes::VIEW => Attributes::VIEW_PRODUCTS,
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
}
