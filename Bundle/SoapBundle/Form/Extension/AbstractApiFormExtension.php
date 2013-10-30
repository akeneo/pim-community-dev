<?php

namespace Oro\Bundle\SoapBundle\Form\Extension;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\AbstractTypeExtension;

use Oro\Bundle\FormBundle\Form\Type\OroDateType;
use Oro\Bundle\FormBundle\Form\Type\OroDateTimeType;

abstract class AbstractApiFormExtension extends AbstractTypeExtension
{
    /**
     * @var Request
     */
    protected $request;

    /**
     * @var string
     */
    protected $apiUrlPrefix;

    /**
     * @var boolean
     */
    protected $isApiRequest;

    /**
     * @var array
     */
    protected $dateFormTypes = array(
        OroDateType::NAME,
        OroDateTimeType::NAME,
    );

    /**
     * @param Request $request
     * @param string $apiUrlPrefix
     */
    public function __construct(Request $request, $apiUrlPrefix)
    {
        $this->request = $request;
        $this->apiUrlPrefix = $apiUrlPrefix;
    }

    /**
     * @return bool
     */
    protected function isApiRequest()
    {
        if (null === $this->isApiRequest) {
            $pathInfo = $this->request->getPathInfo();
            $this->isApiRequest = strpos($pathInfo, $this->apiUrlPrefix) === 0;
        }

        return $this->isApiRequest;
    }
}
