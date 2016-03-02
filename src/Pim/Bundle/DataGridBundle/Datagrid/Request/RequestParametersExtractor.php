<?php

namespace Pim\Bundle\DataGridBundle\Datagrid\Request;

use Oro\Bundle\DataGridBundle\Datagrid\RequestParameters;
use Symfony\Component\HttpFoundation\Request;

/**
 * Extract request parameters from Oro RequestParameters and fallback on Request, idea is to wrap
 * the use of RequestParameters which disappears in future Oro version
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RequestParametersExtractor implements RequestParametersExtractorInterface
{
    /** @var RequestParameters */
    protected $requestParams;

    /** @var Request */
    protected $request;

    /**
     * @param RequestParameters $requestParams
     */
    public function __construct(RequestParameters $requestParams)
    {
        $this->requestParams = $requestParams;
    }

    /**
     * @param Request $request
     */
    public function setRequest(Request $request = null)
    {
        $this->request = $request;
    }

    /**
     * {@inheritdoc}
     */
    public function getParameter($key)
    {
        $param = $this->requestParams->get($key, null);
        if ($param === null) {
            $param = $this->request->get($key, null);
        }
        if ($param === null) {
            throw new \LogicException(sprintf('Parameter "%s" is expected', $key));
        }

        return $param;
    }

    /**
     * {@inheritdoc}
     */
    public function getDatagridParameter($key, $defaultValue = null)
    {
        return $this->requestParams->get($key, $defaultValue);
    }

    /**
     * {@inheritdoc}
     */
    public function getRequestParameter($key, $defaultValue = null)
    {
        return $this->request->get($key, $defaultValue);
    }
}
