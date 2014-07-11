<?php

namespace PimEnterprise\Bundle\SecurityBundle\Voter;

use PimEnterprise\Bundle\SecurityBundle\Entity\Repository\CategoryAccessRepository;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use PimEnterprise\Bundle\SecurityBundle\Attributes;

/**
 * Product voter, allows to know if products can be edited or consulted by a
 * user depending on his roles
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
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
        return in_array($attribute, [Attributes::VIEW_PRODUCT, Attributes::EDIT_PRODUCT]);
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
     * - a product is accessible when it's not at least in a category
     * - then we apply category's permissions
     *
     * @param ProductInterface $product
     * @param UserInterface    $user
     * @param string           $attribute
     *
     * @return bool
     */
    protected function isProductAccessible(ProductInterface $product, UserInterface $user, $attribute)
    {
        if (count($product->getCategories()) === 0) {
            return true;
        }

        $categoryAttribute = (Attributes::EDIT_PRODUCT === $attribute) ?
            Attributes::EDIT_PRODUCTS :
            Attributes::VIEW_PRODUCTS;

        $categoryIds = [];
        foreach ($product->getCategories() as $category) {
            $categoryIds[] = $category->getId();
        }
        $grantedCategoryIds = $this->categoryAccessRepo->getGrantedCategoryIds($user, $categoryAttribute);

        $intersection = array_intersect($categoryIds, $grantedCategoryIds);
        if (count($intersection)) {
            return true;
        }

        return false;
    }
}
