<?php

namespace Pim\Bundle\EnrichBundle\MassEditAction\Operation;

/**
 * Operation to execute on a set of items
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface MassEditOperationInterface
{
    public function getAlias();

    public function getFilters();

    public function setFilters(array $filters);

    public function getActions();

    public function setActions(array $actions);
}
