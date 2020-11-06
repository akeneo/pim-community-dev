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
     */
    public function getParameter(string $key): string;

    /**
     * Return the datagrid parameter
     *
     * @param string $key
     * @param mixed  $defaultValue
     */
    public function getDatagridParameter(string $key, $defaultValue = null): string;

    /**
     * Return the request parameter
     *
     * @param string $key
     * @param mixed  $defaultValue
     */
    public function getRequestParameter(string $key, $defaultValue = null): string;
}
