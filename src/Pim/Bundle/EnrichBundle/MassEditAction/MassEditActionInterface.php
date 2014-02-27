<?php

namespace Pim\Bundle\EnrichBundle\MassEditAction;

use Doctrine\ORM\QueryBuilder;

/**
 * Operation to execute on a set of products
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface MassEditActionInterface
{
    /**
     * Get the form type to use in order to configure the operation
     *
     * @return string|FormTypeInterface
     */
    public function getFormType();

    /**
     * Get the form options to configure the operation
     *
     * @return array
     */
    public function getFormOptions();

    /**
     * Initialize the operation with the products
     *
     * @param QueryBuilder $qb
     */
    public function initialize(QueryBuilder $qb);

    /**
     * Perform an operation on a set of products
     *
     * @param QueryBuilder $qb
     */
    public function perform(QueryBuilder $qb);
}
