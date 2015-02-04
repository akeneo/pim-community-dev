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
use Pim\Bundle\CatalogBundle\Repository\AttributeRepositoryInterface;
use Pim\Bundle\CatalogBundle\Repository\ProductMassActionRepositoryInterface;
use PimEnterprise\Bundle\SecurityBundle\Attributes;
use PimEnterprise\Bundle\SecurityBundle\Entity\Repository\AttributeGroupAccessRepository;
use Symfony\Component\Security\Core\SecurityContext;

/**
 * Override product mass action manager
 *
 * @author Romain Monceau <romain@akeneo.com>
 */
class ProductMassActionManager extends BaseProductMassActionManager
{
    /**
     * @var SecurityContext
     */
    protected $securityContext;

    /**
     * @var AttributeGroupAccessRepository
     */
    protected $attGroupAccessRepo;

    /**
     * Construct
     *
     * @param ProductMassActionRepositoryInterface $massActionRepository
     * @param AttributeRepositoryInterface         $attributeRepository
     * @param AttributeGroupAccessRepository       $attGroupAccessRepo
     * @param SecurityContext                      $securityContext
     */
    public function __construct(
        ProductMassActionRepositoryInterface $massActionRepository,
        AttributeRepositoryInterface $attributeRepository,
        AttributeGroupAccessRepository $attGroupAccessRepo,
        SecurityContext $securityContext
    ) {
        parent::__construct($massActionRepository, $attributeRepository);

        $this->attGroupAccessRepo = $attGroupAccessRepo;
        $this->securityContext    = $securityContext;
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
            ->getGrantedAttributeGroupQB($this->securityContext->getToken()->getUser(), Attributes::EDIT_ATTRIBUTES);

        return $this
            ->attributeRepository
            ->findWithGroups(
                array_unique($attributeIds),
                array(
                    'conditions' => ['unique' => 0],
                    'filters'    => ['g.id' => $subQB]
                )
            );
    }
}
