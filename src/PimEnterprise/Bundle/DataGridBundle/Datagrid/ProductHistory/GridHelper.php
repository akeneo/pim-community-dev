<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\DataGridBundle\Datagrid\ProductHistory;

use Oro\Bundle\DataGridBundle\Datasource\ResultRecordInterface;
use Pim\Bundle\CatalogBundle\Repository\ProductRepositoryInterface;
use PimEnterprise\Bundle\SecurityBundle\Attributes;
use Symfony\Component\Security\Core\SecurityContextInterface;

/**
 * Helper for product history to display revert action
 *
 * @author Romain Monceau <romain@akeneo.com>
 */
class GridHelper
{
    /** @var \Symfony\Component\Security\Core\SecurityContextInterface */
    protected $securityContext;

    /** @var \Pim\Bundle\CatalogBundle\Repository\ProductRepositoryInterface */
    protected $productRepository;

    /**
     * @param SecurityContextInterface   $securityContext
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        SecurityContextInterface $securityContext,
        ProductRepositoryInterface $productRepository
    ) {
        $this->securityContext   = $securityContext;
        $this->productRepository = $productRepository;
    }

    /**
     * Returns a callback to ease the configuration of different actions for each row
     *
     * @return callable
     */
    public function getActionConfigurationClosure()
    {
        return function (ResultRecordInterface $record) {
            $product = $this->productRepository->findOneById($record->getValue('resourceId'));
            $ownershipGranted = $this->securityContext->isGranted(Attributes::OWN, $product);

            return [
                'revert' => $ownershipGranted
            ];
        };
    }
}
