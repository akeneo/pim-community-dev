<?php

namespace PimEnterprise\Bundle\DataGridBundle\Datagrid\Product;

use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Doctrine\ORM\EntityRepository;
use Oro\Bundle\DataGridBundle\Datagrid\RequestParameters;
use Pim\Bundle\DataGridBundle\Datagrid\Product\ContextConfigurator as PimContextConfigurator;
use Pim\Bundle\CatalogBundle\Manager\ProductManager;
use PimEnterprise\Bundle\UserBundle\Context\UserContext;
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
    /** @staticvar string */
    const CURRENT_TREE_ID_KEY = 'current_tree_id';

    /**
     * @var AttributeGroupAccessRepository
     */
    protected $accessRepository;

    /**
     * @param integer[]
     */
    protected $grantedGroupIds;

    /**
     * @var UserContext
     */
    protected $userContext;

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
     * {@inheritdoc}
     */
    public function configure(DatagridConfiguration $configuration)
    {
        $this->configuration = $configuration;
        parent::configure($configuration);
        $this->addCurrentTreeId();
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

    /**
     * Inject current tree id in the datagrid configuration
     */
    protected function addCurrentTreeId()
    {
        $treeId = $this->getTreeId();
        $path = $this->getSourcePath(self::CURRENT_TREE_ID_KEY);
        $this->configuration->offsetSetByPath($path, $treeId);
    }

    /**
     * Get current tree from datagrid parameters, then user config
     *
     * @return string
     */
    protected function getTreeId()
    {
        $filterValues = $this->requestParams->get('_filter');
        if (
            isset($filterValues['category']['value']['treeId']) &&
            null !== $filterValues['category']['value']['treeId']
        ) {
            return $filterValues['category']['value']['treeId'];
        } else {
            $tree = $this->userContext->getAccessibleUserTree();

            return $tree->getId();
        }
    }
}
