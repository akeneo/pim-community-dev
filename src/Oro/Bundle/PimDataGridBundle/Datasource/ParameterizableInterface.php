<?php

namespace Oro\Bundle\PimDataGridBundle\Datasource;

/**
 * Parameterizable interface
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ParameterizableInterface
{
    /**
     * @return array
     */
    public function getParameters();

    /**
     * @param array $parameters
     *
     * @return ParameterizableInterface
     */
    public function setParameters($parameters);
}
