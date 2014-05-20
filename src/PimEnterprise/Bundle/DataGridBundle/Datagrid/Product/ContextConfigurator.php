<?php

namespace PimEnterprise\Bundle\DataGridBundle\Datagrid\Product;

use Symfony\Component\Security\Core\SecurityContextInterface;
use Oro\Bundle\DataGridBundle\Datagrid\RequestParameters;
use Pim\Bundle\DataGridBundle\Datagrid\Product\ContextConfigurator as PimContextConfigurator;
use Pim\Bundle\CatalogBundle\Manager\ProductManager;
use Pim\Bundle\CatalogBundle\Entity\Repository\AttributeGroupRepository;
use Doctrine\ORM\EntityRepository;
use PimEnterprise\Bundle\SecurityBundle\Voter\AttributeGroupVoter;

/**
 * Override context configurator to apply rights on attribute groups
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class ContextConfigurator extends PimContextConfigurator
{
    /**
     * @var AttributeGroupRepository
     */
    protected $groupRepository;

    /**
     * @param integer[]
     */
    protected $allowedGroupIds = null;

    /**
     * @param ProductManager           $productManager
     * @param RequestParameters        $requestParams
     * @param SecurityContextInterface $securityContext
     * @param EntityRepository         $gridViewRepository
     * @param AttributeGroupRepository $groupRepository
     */
    public function __construct(
        ProductManager $productManager,
        RequestParameters $requestParams,
        SecurityContextInterface $securityContext,
        EntityRepository $gridViewRepository,
        AttributeGroupRepository $groupRepository
    ) {
        parent::__construct($productManager, $requestParams, $securityContext, $gridViewRepository);
        $this->groupRepository = $groupRepository;
    }

    /**
     * Override to filter per rights per groups too
     *
     * @param string[] $attributeCodes
     *
     * @return integer[]
     */
    protected function getAttributeIds($attributeCodes = null)
    {
        $repository   = $this->productManager->getAttributeRepository();
        $groupIds     = $this->getAllowedGroupIds();
        $attributeIds = $repository->getAttributeIdsUseableInGrid($attributeCodes, $groupIds);

        return $attributeIds;
    }

    /**
     * Get allowed group ids
     *
     * @return integer[]
     */
    protected function getAllowedGroupIds()
    {
        if (!$this->allowedGroupIds) {
            // TODO : use getGrantedAttributeGroupQB, wait for the merge of PR 20
            $groups = $this->groupRepository->findAll();
            $this->allowedGroupIds = [];
            foreach ($groups as $group) {
                if ($this->securityContext->isGranted(AttributeGroupVoter::VIEW_ATTRIBUTES, $group)) {
                    $this->allowedGroupIds[]= $group->getId();
                }
            }
        }

        return $this->allowedGroupIds;
    }
}
