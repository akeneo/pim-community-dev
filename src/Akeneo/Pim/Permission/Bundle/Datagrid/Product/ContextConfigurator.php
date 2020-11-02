<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Permission\Bundle\Datagrid\Product;

use Akeneo\Pim\Enrichment\Component\Product\Repository\GroupRepositoryInterface;
use Akeneo\Pim\Permission\Bundle\Entity\Repository\AttributeGroupAccessRepository;
use Akeneo\Pim\Permission\Bundle\User\UserContext;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Datagrid\RequestParameters;
use Oro\Bundle\PimDataGridBundle\Datagrid\Configuration\Product\ContextConfigurator as BaseContextConfigurator;
use Symfony\Component\HttpFoundation\RequestStack;

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

    /** @var UserContext */
    protected $userContext;

    /**
     * @param ObjectRepository               $productRepository
     * @param RequestParameters              $requestParams
     * @param UserContext                    $userContext
     * @param ObjectManager                  $objectManager
     * @param GroupRepositoryInterface       $productGroupRepository
     * @param RequestStack                   $requestStack
     * @param AttributeGroupAccessRepository $accessRepository
     */
    public function __construct(
        ObjectRepository $productRepository,
        RequestParameters $requestParams,
        UserContext $userContext,
        ObjectManager $objectManager,
        GroupRepositoryInterface $productGroupRepository,
        RequestStack $requestStack,
        AttributeGroupAccessRepository $accessRepository
    ) {
        parent::__construct(
            $productRepository,
            $requestParams,
            $userContext,
            $objectManager,
            $productGroupRepository,
            $requestStack
        );

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
     * Inject current tree id in the datagrid configuration
     */
    protected function addCurrentTreeId()
    {
        $treeId = $this->getTreeId();

        if (null !== $treeId) {
            $path = $this->getSourcePath(self::CURRENT_TREE_ID_KEY);
            $this->configuration->offsetSetByPath($path, $treeId);
        }
    }

    /**
     * Get current tree from datagrid parameters, then user config
     *
     * @return int|null
     */
    protected function getTreeId()
    {
        $filterValues = $this->requestParams->get('_filter');
        if (isset($filterValues['category']['value']['treeId']) && $filterValues['category']['value']['treeId']) {
            return $filterValues['category']['value']['treeId'];
        } else {
            try {
                $tree = $this->userContext->getAccessibleUserTree();
                if ($tree !== null) {
                    return $tree->getId();
                }
            } catch (\LogicException $e) {
                return null;
            }
        }

        return null;
    }
}
