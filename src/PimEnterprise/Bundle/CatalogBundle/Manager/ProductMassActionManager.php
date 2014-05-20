<?php

namespace PimEnterprise\Bundle\CatalogBundle\Manager;

use Symfony\Component\Security\Core\SecurityContext;

use Pim\Bundle\CatalogBundle\Repository\ProductMassActionRepositoryInterface;
use Pim\Bundle\CatalogBundle\Entity\Repository\AttributeRepository;
use Pim\Bundle\CatalogBundle\Manager\ProductMassActionManager as PimProductMassActionManager;

use PimEnterprise\Bundle\SecurityBundle\Entity\Repository\AttributeGroupAccessRepository;

/**
 * Override product mass action manager
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class ProductMassActionManager extends PimProductMassActionManager
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
     * @param AttributeRepository                  $attributeRepository
     * @param AttributeGroupAccessRepository       $attGroupAccessRepo
     * @param SecurityContext                      $securityContext
     */
    public function __construct(
        ProductMassActionRepositoryInterface $massActionRepository,
        AttributeRepository $attributeRepository,
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
    public function findCommonAttributes(array $productIds)
    {
        $attributeIds = $this->massActionRepository->findCommonAttributeIds($productIds);

        $subQB = $this
            ->attGroupAccessRepo
            ->getGrantedAttributeGroupQB($this->securityContext->getUser(), 'EDIT');

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
