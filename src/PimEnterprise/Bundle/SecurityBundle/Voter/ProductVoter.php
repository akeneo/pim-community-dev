<?php

namespace PimEnterprise\Bundle\SecurityBundle\Voter;

use Pim\Bundle\CatalogBundle\Model\AbstractProduct;
use PimEnterprise\Bundle\SecurityBundle\Entity\Repository\CategoryAccessRepository;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Product voter, allows to know if products can be edited or consulted by a
 * user depending on his roles
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class ProductVoter implements VoterInterface
{
    /** @staticvar string */
    const PRODUCT_VIEW = 'PRODUCT_VIEW';

    /** @staticvar string */
    const PRODUCT_EDIT = 'PRODUCT_EDIT';

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
        return in_array($attribute, [ProductVoter::PRODUCT_VIEW, ProductVoter::PRODUCT_EDIT]);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsClass($class)
    {
        return $class instanceof AbstractProduct;
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
                    $result       = VoterInterface::ACCESS_DENIED;

                    if ($this->isProductAccessible($object, $token->getUser(), $attribute)) {
                        return VoterInterface::ACCESS_GRANTED;
                    }

                }
            }
        }

        return $result;
    }

    /**
     * Determine if a product is accessible for the user
     *
     * @param AbstractProduct $product
     * @param UserInterface   $user
     * @param string          $attribute
     *
     * @return bool
     */
    protected function isProductAccessible(AbstractProduct $product, UserInterface $user, $attribute)
    {
        $categoryAttribute = (ProductVoter::PRODUCT_EDIT === $attribute) ?
            CategoryVoter::EDIT_PRODUCTS :
            CategoryVoter::VIEW_PRODUCTS;

        $accessibleTreeIds = $this->categoryAccessRepo->getGrantedCategoryIds($user, $categoryAttribute);

        $intersection = array_intersect($product->getTreeIds(), $accessibleTreeIds);
        if (count($intersection)) {
            return true;
        }

        return false;
    }
}
