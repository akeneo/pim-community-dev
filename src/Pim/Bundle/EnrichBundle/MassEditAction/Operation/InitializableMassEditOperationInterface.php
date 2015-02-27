<?php

namespace Pim\Bundle\EnrichBundle\MassEditAction\Operation;

/**
 * Operation to execute on a set of products
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface InitializableMassEditOperationInterface
{
    /**
     * Initialize the operation
     */
    public function initialize();
}
