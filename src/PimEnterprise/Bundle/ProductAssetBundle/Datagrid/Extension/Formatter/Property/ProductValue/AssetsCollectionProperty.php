<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\ProductAssetBundle\Datagrid\Extension\Formatter\Property\ProductValue;

use Pim\Bundle\DataGridBundle\Datagrid\Request\RequestParametersExtractorInterface;
use Pim\Bundle\DataGridBundle\Extension\Formatter\Property\ProductValue\TwigProperty;
use PimEnterprise\Bundle\UserBundle\Context\UserContext;

/**
 * Assets collection property for a product
 *
 * @author Yohan Blain <yohan.blain@akeneo.com>
 */
class AssetsCollectionProperty extends TwigProperty
{
    /** @var RequestParametersExtractorInterface */
    protected $paramsExtractor;

    /** @var UserContext */
    protected $userContext;

    /**
     * @param \Twig_Environment                   $environment
     * @param RequestParametersExtractorInterface $paramsExtractor
     * @param UserContext                         $userContext
     */
    public function __construct(
        \Twig_Environment $environment,
        RequestParametersExtractorInterface $paramsExtractor,
        UserContext $userContext
    ) {
        parent::__construct($environment);

        $this->paramsExtractor = $paramsExtractor;
        $this->userContext = $userContext;
    }

    /**
     * {@inheritdoc}
     */
    protected function format($value)
    {
        if (isset($value['data']) && !empty($value['data'])) {
            $code = explode(',', $value['data'])[0];

            return $this->getTemplate()->render(
                [
                    'code'        => $code,
                    'channelCode' => $this->getCurrentChannelCode(),
                    'localeCode'  => $this->getCurrentLocaleCode()
                ]
            );
        }

        return null;
    }

    /**
     * Return the current locale code from datagrid parameters, then request parameters
     *
     * @return string
     */
    protected function getCurrentLocaleCode()
    {
        return $this->paramsExtractor->getParameter('dataLocale');
    }

    /**
     * Return the current channel code from datagrid parameters, then request parameters, then user config
     *
     * @return string
     */
    protected function getCurrentChannelCode()
    {
        $channelCode = null;

        $filterValues = $this->paramsExtractor->getDatagridParameter('_filter');
        if (isset($filterValues['scope']['value'])) {
            $channelCode = $filterValues['scope']['value'];
        }

        if (null === $channelCode) {
            $requestFilters = $this->paramsExtractor->getRequestParameter('filters');
            if (isset($requestFilters['scope']['value'])) {
                $channelCode = $requestFilters['scope']['value'];
            }
        }

        if (null === $channelCode) {
            $channelCode = $this->userContext->getUserChannelCode();
        }

        return $channelCode;
    }
}
