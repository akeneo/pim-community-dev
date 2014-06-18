<?php

namespace PimEnterprise\Bundle\DataGridBundle\Datagrid\Product;

use Symfony\Component\Security\Core\SecurityContextInterface;
use Doctrine\ORM\EntityRepository;
use Oro\Bundle\DataGridBundle\Datagrid\RequestParameters;
use Pim\Bundle\DataGridBundle\Datagrid\Product\ContextConfigurator as PimContextConfigurator;
use Pim\Bundle\CatalogBundle\Manager\ProductManager;
use Pim\Bundle\UserBundle\Context\UserContext;
use PimEnterprise\Bundle\SecurityBundle\Entity\Repository\AttributeGroupAccessRepository;
use PimEnterprise\Bundle\SecurityBundle\Voter\AttributeGroupVoter;

/**
 * Override context configurator to apply permissions on attribute groups
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class ContextConfigurator extends PimContextConfigurator
{
    /**
     * @var AttributeGroupAccessRepository
     */
    protected $accessRepository;

    /**
     * @param integer[]
     */
    protected $grantedGroupIds = null;

    /**
     * @param ProductManager                 $productManager
     * @param RequestParameters              $requestParams
     * @param UserContext                    $userContext
     * @param EntityRepository               $gridViewRepository
     * @param AttributeGroupAccessRepository $accessRepository
     */
    public function __construct(
        ProductManager $productManager,
        RequestParameters $requestParams,
        UserContext $userContext,
        EntityRepository $gridViewRepository,
        AttributeGroupAccessRepository $accessRepository
    ) {
        parent::__construct($productManager, $requestParams, $userContext, $gridViewRepository);
        $this->accessRepository = $accessRepository;
    }

    /**
     * Override to filter per permissions per groups too
     *
     * @param string[] $attributeCodes
     *
     * @return integer[]
     */
    protected function getAttributeIds($attributeCodes = null)
    {
        $repository   = $this->productManager->getAttributeRepository();
        $groupIds     = $this->getGrantedGroupIds();
        $attributeIds = $repository->getAttributeIdsUseableInGrid($attributeCodes, $groupIds);

        return $attributeIds;
    }

    /**
     * Get allowed group ids
     *
     * @return integer[]
     */
    protected function getGrantedGroupIds()
    {
        if (!$this->grantedGroupIds) {
            $result = $this->accessRepository
                ->getGrantedAttributeGroupQB($this->userContext->getUser(), AttributeGroupVoter::VIEW_ATTRIBUTES)
                ->getQuery()
                ->getArrayResult();

            $this->grantedGroupIds = array_map(
                function ($row) {
                    return $row['id'];
                },
                $result
            );
        }

        return $this->grantedGroupIds;
    }
}
