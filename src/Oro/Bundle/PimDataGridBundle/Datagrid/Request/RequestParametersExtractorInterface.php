<?php

namespace Oro\Bundle\PimDataGridBundle\Datagrid\Request;

/**
 * Extract request parameters from Oro RequestParameters and fallback on Request, idea is to wrap
 * the use of RequestParameters which disappears in future Oro version
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface RequestParametersExtractorInterface
{
    /**
     * Return the parameter
     *
     * @param string $key
     *
     * @return string
     */
    public function getParameter($key);

    /**
     * Return the datagrid parameter
     *
     * @param string $key
     * @param mixed  $defaultValue
     *
     * @return string
     */
    public function getDatagridParameter($key, $defaultValue = null);

    /**
     * Return the request parameter
     *
     * @param string $key
     * @param mixed  $defaultValue
     *
     * @return string
     */
    public function getRequestParameter($key, $defaultValue = null);
}
