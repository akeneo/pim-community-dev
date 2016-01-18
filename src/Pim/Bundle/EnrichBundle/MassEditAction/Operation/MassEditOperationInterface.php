<?php

namespace Pim\Bundle\EnrichBundle\MassEditAction\Operation;

/**
 * Basic mass edit operation to execute on a set of items
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface MassEditOperationInterface
{
    /**
     * Get alias of this mass edit operation
     *
     * @return string
     */
    public function getOperationAlias();

    /**
     * Get filters to retrieve items for this operation
     *
     * @return array
     */
    public function getFilters();

    /**
     * @param array $filters
     *
     * @return MassEditOperationInterface
     */
    public function setFilters(array $filters);

    /**
     * Get actions this operation is going to apply on each item
     *
     * @return array
     */
    public function getActions();

    /**
     * @param array $actions
     *
     * @return MassEditOperationInterface
     */
    public function setActions(array $actions);

    /**
     * @return string
     */
    public function getItemsName();

    /**
     * Return this operation instance
     *
     * @return mixed
     */
    public function getOperation();
}
