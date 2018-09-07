<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\Workflow\Bundle\Datagrid\Configuration\Proposal;

use Akeneo\Pim\Permission\Bundle\User\UserContext;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Datagrid\RequestParameters;
use Oro\Bundle\DataGridBundle\Extension\Toolbar\ToolbarExtension;
use Oro\Bundle\PimDataGridBundle\Datagrid\Configuration\ConfiguratorInterface;
use Oro\Bundle\PimDataGridBundle\Extension\Pager\PagerExtension;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Proposal grid context configurator
 *
 * @author Julien Janvier <julien.janvier@akeneo.com>
 */
class ContextConfigurator implements ConfiguratorInterface
{
    /** @var RequestParameters */
    protected $requestParams;

    /** @var UserContext */
    protected $userContext;

    /** @var RequestStack */
    protected $requestStack;

    /** @var AttributeRepositoryInterface */
    protected $attributeRepository;

    /** @var DatagridConfiguration */
    protected $configuration;

    /**
     * @param RequestParameters            $requestParams
     * @param UserContext                  $userContext
     * @param RequestStack                 $requestStack
     * @param AttributeRepositoryInterface $attributeRepository
     */
    public function __construct(
        RequestParameters $requestParams,
        UserContext $userContext,
        RequestStack $requestStack,
        AttributeRepositoryInterface $attributeRepository
    ) {
        $this->requestParams = $requestParams;
        $this->userContext = $userContext;
        $this->requestStack = $requestStack;
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function configure(DatagridConfiguration $configuration)
    {
        $this->configuration = $configuration;

        $path = sprintf(self::SOURCE_PATH, self::REPOSITORY_PARAMETERS_KEY);
        $repositoryParams = $this->configuration->offsetGetByPath($path, null);

        if ($repositoryParams) {
            $params = [];
            foreach ($repositoryParams as $paramName) {
                if ('currentUser' === $paramName) {
                    $params[$paramName] = $this->userContext->getUser();
                } else {
                    $params[$paramName] = $this->requestParams->get($paramName, $this->getRequest()->get($paramName, null));
                }
            }
            $this->configuration->offsetSetByPath($path, $params);
        }

        $this->addAttributesConfig();
        $this->addPaginationConfig();
        $this->addRepositoryParameters();
    }

    /**
     * @param string $key the configuration key
     *
     * @return string
     */
    protected function getSourcePath($key): string
    {
        return sprintf(self::SOURCE_PATH, $key);
    }

    /**
     * Return usable attribute ids
     *
     * @param string[]|null $attributeCodes
     *
     * @return integer[]
     */
    protected function getAttributeIdsUseableInGrid($attributeCodes = null): array
    {
        return $this->attributeRepository->getAttributeIdsUseableInGrid($attributeCodes);
    }

    /**
     * Inject attributes configurations in the datagrid configuration
     */
    protected function addAttributesConfig(): void
    {
        $attributes = $this->getAttributesConfig();
        $path = $this->getSourcePath(self::USEABLE_ATTRIBUTES_KEY);
        $this->configuration->offsetSetByPath($path, $attributes);
    }

    /**
     * Get current locale from datagrid parameters, then request parameters, then user config
     *
     * @return string
     */
    protected function getCurrentLocaleCode(): string
    {
        $dataLocale = $this->requestParams->get('dataLocale', null);
        if (!$dataLocale) {
            $dataLocale = $this->getRequest()->get('dataLocale', null);
        }
        if (!$dataLocale && $locale = $this->userContext->getUser()->getCatalogLocale()) {
            $dataLocale = $locale->getCode();
        }

        return $dataLocale;
    }

    /**
     * Get attributes configuration for attribute that can be used in grid (as column or filter)
     *
     * @return array
     */
    protected function getAttributesConfig(): array
    {
        $attributeIds = $this->getAttributeIdsUseableInGrid();
        if (empty($attributeIds)) {
            return [];
        }

        $currentLocale = $this->getCurrentLocaleCode();

        return $this->attributeRepository->getAttributesAsArray(true, $currentLocale, $attributeIds);
    }

    /**
     * @return Request|null
     */
    protected function getRequest(): ?Request
    {
        return $this->requestStack->getCurrentRequest();
    }

    /**
     * Inject requested _per_page parameters in the datagrid configuration
     */
    protected function addPaginationConfig()
    {
        $pager = $this->requestParams->get(PagerExtension::PAGER_ROOT_PARAM);

        $defaultPerPage = $this->configuration->offsetGetByPath(
            ToolbarExtension::PAGER_DEFAULT_PER_PAGE_OPTION_PATH,
            25
        );
        $itemsPerPage = isset($pager[PagerExtension::PER_PAGE_PARAM]) ? (int)$pager[PagerExtension::PER_PAGE_PARAM] : $defaultPerPage;

        $this->configuration->offsetSetByPath($this->getSourcePath(PagerExtension::PER_PAGE_PARAM), $itemsPerPage);

        $currentPage = isset($pager[PagerExtension::PAGE_PARAM]) ? (int)$pager[PagerExtension::PAGE_PARAM] : 1;
        $from = ($currentPage - 1) * $itemsPerPage;
        $this->configuration->offsetSetByPath($this->getSourcePath('from'), $from);
    }

    /**
     * Inject requested repository parameters in the datagrid configuration
     */
    protected function addRepositoryParameters()
    {
        $path = $this->getSourcePath(self::REPOSITORY_PARAMETERS_KEY);
        $repositoryParams = $this->configuration->offsetGetByPath($path, null);

        if ($repositoryParams) {
            $params = [];
            foreach ($repositoryParams as $paramName) {
                $params[$paramName] = $this->requestParams->get($paramName, $this->getRequest()->get($paramName, null));
            }
            $this->configuration->offsetSetByPath($path, $params);
        }
    }
}
