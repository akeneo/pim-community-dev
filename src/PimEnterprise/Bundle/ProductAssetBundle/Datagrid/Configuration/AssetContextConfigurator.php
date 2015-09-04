<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\ProductAssetBundle\Datagrid\Configuration;

use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Pim\Bundle\DataGridBundle\Datagrid\Configuration\ConfiguratorInterface;
use Pim\Bundle\DataGridBundle\Datagrid\Request\RequestParametersExtractorInterface;
use PimEnterprise\Bundle\UserBundle\Context\UserContext;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Asset grid context configurator
 *
 * @author Marie Bochu <marie.bochu@akeneo.com>
 */
class AssetContextConfigurator implements ConfiguratorInterface
{
    /** @var RequestParametersExtractorInterface */
    protected $paramsExtractor;

    /** @var UserContext */
    protected $userContext;

    /** @var DatagridConfiguration */
    protected $configuration;

    /**
     * @param RequestParametersExtractorInterface $paramsExtractor
     * @param UserContext                         $userContext
     */
    public function __construct(RequestParametersExtractorInterface $paramsExtractor, UserContext $userContext)
    {
        $this->paramsExtractor = $paramsExtractor;
        $this->userContext     = $userContext;
    }

    /**
     * {@inheritdoc}
     */
    public function configure(DatagridConfiguration $configuration)
    {
        $this->configuration = $configuration;
        $this->addLocaleCode();
    }

    /**
     * Inject current locale code in the datagrid configuration
     */
    protected function addLocaleCode()
    {
        $localeCode = $this->getCurrentLocaleCode();
        $path = $this->getSourcePath(self::DISPLAYED_LOCALE_KEY);
        $this->configuration->offsetSetByPath($path, $localeCode);
    }

    /**
     * Get current locale from datagrid parameters, then request parameters, then user config
     *
     * @throws \LogicException
     *
     * @return string
     */
    protected function getCurrentLocaleCode()
    {
        $dataLocale = null;

        try {
            $dataLocale = $this->paramsExtractor->getParameter('dataLocale');
        } catch (\LogicException $e) {
            if ($locale = $this->userContext->getUser()->getCatalogLocale()) {
                $dataLocale = $locale->getCode();
            }
        }

        return $dataLocale;
    }

    /**
     * @param string $key the configuration key
     *
     * @return string
     */
    protected function getSourcePath($key)
    {
        return sprintf(self::SOURCE_PATH, $key);
    }
}
