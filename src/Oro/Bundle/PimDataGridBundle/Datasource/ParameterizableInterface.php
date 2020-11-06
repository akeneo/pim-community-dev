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
    public function getParameters(): array;

    /**
     * @param array $parameters
     */
    public function setParameters(array $parameters): \Oro\Bundle\PimDataGridBundle\Datasource\ParameterizableInterface;
}
