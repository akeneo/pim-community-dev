<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\CatalogBundle\Manager;

use Pim\Bundle\CatalogBundle\Manager\ProductMassActionManager as BaseProductMassActionManager;
use Pim\Bundle\CatalogBundle\Repository\ProductMassActionRepositoryInterface;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use PimEnterprise\Bundle\SecurityBundle\Attributes;
use PimEnterprise\Bundle\SecurityBundle\Entity\Repository\AttributeGroupAccessRepository;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Override product mass action manager
 *
 * @author Romain Monceau <romain@akeneo.com>
 */
class ProductMassActionManager extends BaseProductMassActionManager
{
    /** @var TokenStorageInterface */
    protected $tokenStorage;

    /** @var AttributeGroupAccessRepository */
    protected $attGroupAccessRepo;

    /**
     * Construct
     *
     * @param ProductMassActionRepositoryInterface $massActionRepository
     * @param AttributeRepositoryInterface         $attributeRepository
     * @param AttributeGroupAccessRepository       $attGroupAccessRepo
     * @param TokenStorageInterface                $tokenStorage
     */
    public function __construct(
        ProductMassActionRepositoryInterface $massActionRepository,
        AttributeRepositoryInterface $attributeRepository,
        AttributeGroupAccessRepository $attGroupAccessRepo,
        TokenStorageInterface $tokenStorage
    ) {
        parent::__construct($massActionRepository, $attributeRepository);

        $this->attGroupAccessRepo = $attGroupAccessRepo;
        $this->tokenStorage       = $tokenStorage;
    }

    /**
     * {@inheritdoc}
     */
    public function findCommonAttributes(array $products)
    {
        $productIds = [];
        foreach ($products as $product) {
            $productIds[] = $product->getId();
        }
        $attributeIds = $this->massActionRepository->findCommonAttributeIds($productIds);

        $subQB = $this
            ->attGroupAccessRepo
            ->getGrantedAttributeGroupQB($this->tokenStorage->getToken()->getUser(), Attributes::EDIT_ATTRIBUTES);

        return $this
            ->attributeRepository
            ->findWithGroups(
                array_unique($attributeIds),
                [
                    'conditions' => ['unique' => 0],
                    'filters'    => ['g.id'   => $subQB]
                ]
            );
    }
}
