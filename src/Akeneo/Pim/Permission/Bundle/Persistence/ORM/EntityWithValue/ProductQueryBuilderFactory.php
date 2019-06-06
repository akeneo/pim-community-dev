<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Permission\Bundle\Persistence\ORM\EntityWithValue;

use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderInterface;
use Akeneo\Pim\Permission\Bundle\Enrichment\Storage\Sql\Category\GetGrantedCategoryCodes;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Creates and configures the product query builder filtered by granted categories
 *
 * @author Marie Bochu <marie.bochu@akeneo.com>
 */
class ProductQueryBuilderFactory implements ProductQueryBuilderFactoryInterface
{
    /** @var ProductQueryBuilderFactoryInterface */
    private $pqbFactory;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var GetGrantedCategoryCodes */
    private $getAllGrantedCategoryCodes;

    public function __construct(
        ProductQueryBuilderFactoryInterface $pqbFactory,
        TokenStorageInterface $tokenStorage,
        GetGrantedCategoryCodes $getAllGrantedCategoryCodes
    ) {
        $this->pqbFactory = $pqbFactory;
        $this->tokenStorage = $tokenStorage;
        $this->getAllGrantedCategoryCodes = $getAllGrantedCategoryCodes;
    }

    /**
     * Create a product query builder with only granted categories
     *
     * @param array $options
     *
     * @return ProductQueryBuilderInterface
     */
    public function create(array $options = []): ProductQueryBuilderInterface
    {
        $token = $this->tokenStorage->getToken();
        if (null === $token) {
            throw new \LogicException('Token cannot be null on the instantiation of the Product Query Builder.');
        }

        $pqb = $this->pqbFactory->create($options);

        $user = $token->getUser();
        if (!$user instanceof UserInterface) {
            throw new \RuntimeException("The user given is not a user");
        }

        $grantedCategories = $this->getAllGrantedCategoryCodes->forGroupIds($user->getGroupsIds());

        $pqb->addFilter('categories', Operators::IN_LIST_OR_UNCLASSIFIED, $grantedCategories, ['type_checking' => false]);

        return $pqb;
    }
}
