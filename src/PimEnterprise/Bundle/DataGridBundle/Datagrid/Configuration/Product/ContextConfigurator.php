<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\DataGridBundle\Datagrid\Configuration\Product;

use Doctrine\ORM\EntityRepository;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Datagrid\RequestParameters;
use Pim\Bundle\CatalogBundle\Manager\ProductManager;
use Pim\Bundle\CatalogBundle\Repository\AttributeRepositoryInterface;
use Pim\Bundle\DataGridBundle\Datagrid\Configuration\Product\ContextConfigurator as BaseContextConfigurator;
use PimEnterprise\Bundle\SecurityBundle\Attributes;
use PimEnterprise\Bundle\SecurityBundle\Entity\Repository\AttributeGroupAccessRepository;
use PimEnterprise\Bundle\UserBundle\Context\UserContext;

/**
 * Override context configurator to apply permissions on attribute groups
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 */
class ContextConfigurator extends BaseContextConfigurator
{
    /** @staticvar string */
    const CURRENT_TREE_ID_KEY = 'current_tree_id';

    /** @var AttributeGroupAccessRepository */
    protected $accessRepository;

    /** @param integer[] */
    protected $grantedGroupIds;

    /** @var UserContext */
    protected $userContext;

    /**
     * @param ProductManager                 $productManager
     * @param AttributeRepositoryInterface   $attributeRepository
     * @param RequestParameters              $requestParams
     * @param UserContext                    $userContext
     * @param EntityRepository               $gridViewRepository
     * @param AttributeGroupAccessRepository $accessRepository
     */
    public function __construct(
        ProductManager $productManager,
        AttributeRepositoryInterface $attributeRepository,
        RequestParameters $requestParams,
        UserContext $userContext,
        EntityRepository $gridViewRepository,
        AttributeGroupAccessRepository $accessRepository
    ) {
        parent::__construct($productManager, $attributeRepository, $requestParams, $userContext, $gridViewRepository);
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
        $groupIds     = $this->getGrantedGroupIds();
        $attributeIds = $this->attributeRepository->getAttributeIdsUseableInGrid($attributeCodes, $groupIds);

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
                ->getGrantedAttributeGroupQB($this->userContext->getUser(), Attributes::VIEW_ATTRIBUTES)
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
        if (isset($filterValues['category']['value']['treeId']) && $filterValues['category']['value']['treeId']) {
            return $filterValues['category']['value']['treeId'];
        } else {
            $tree = $this->userContext->getAccessibleUserTree();

            return $tree->getId();
        }
    }
}
