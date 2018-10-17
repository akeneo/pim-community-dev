<?php

declare(strict_types=1);

namespace Oro\Bundle\PimDataGridBundle\Datagrid\Configuration\Product;

use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\UserManagement\Bundle\Context\UserContext;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Datagrid\RequestParameters;
use Oro\Bundle\PimDataGridBundle\Datagrid\Configuration\ConfiguratorInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Warning, this will break the datagrid if there's a lot of attributes useable in grid.
 *
 * Inject all the useable attributes in the product grid configuration.
 * For performance reasons, it's better to use the SelectedAttributesConfigurator instead.
 *
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AllAttributesUseableInGridConfigurator implements ConfiguratorInterface
{
    /** @var AttributeRepositoryInterface */
    private $attributeRepository;

    /** @var UserContext */
    private $userContext;

    /** @var RequestParameters */
    private $requestParams;

    /** @var RequestStack */
    private $requestStack;

    /**
     * @param AttributeRepositoryInterface $attributeRepository
     * @param UserContext                  $userContext
     * @param RequestParameters            $requestParams
     * @param RequestStack                 $requestStack
     */
    public function __construct(
        AttributeRepositoryInterface $attributeRepository,
        UserContext $userContext,
        RequestParameters $requestParams,
        RequestStack $requestStack
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->userContext = $userContext;
        $this->requestParams = $requestParams;
        $this->requestStack = $requestStack;
    }

    /**
     * {@inheritdoc}
     */
    public function configure(DatagridConfiguration $configuration)
    {
        $this->addAttributesIds($configuration);
        $this->addAttributesConfig($configuration);
    }

    /**
     * Inject the displayed attribute ids in the datagrid configuration
     *
     * @param DatagridConfiguration $configuration
     */
    private function addAttributesIds(DatagridConfiguration $configuration)
    {
        $attributeCodes = [];
        $params = $this->requestParams->get(RequestParameters::ADDITIONAL_PARAMETERS);

        if (isset($params['view']) && isset($params['view']['columns'])) {
            $attributeCodes = explode(',', $params['view']['columns']);
        }

        $attributeIds = $this->attributeRepository->getAttributeIdsUseableInGrid($attributeCodes);

        $path = $this->getSourcePath(self::DISPLAYED_ATTRIBUTES_KEY);
        $configuration->offsetSetByPath($path, $attributeIds);
    }

    /**
     * Inject attributes configurations in the datagrid configuration
     *
     * @param DatagridConfiguration $configuration
     */
    private function addAttributesConfig(DatagridConfiguration $configuration)
    {
        $attributeIds = $this->attributeRepository->getAttributeIdsUseableInGrid();

        $attributes = [];
        if (!empty($attributeIds)) {
            $currentLocale = $this->getCurrentLocaleCode();
            $attributes = $this->attributeRepository->getAttributesAsArray(true, $currentLocale, $attributeIds);
        }

        $path = $this->getSourcePath(self::USEABLE_ATTRIBUTES_KEY);
        $configuration->offsetSetByPath($path, $attributes);
    }

    /**
     * Get current locale from datagrid parameters, then request parameters, then user config
     *
     * @return string|null
     */
    private function getCurrentLocaleCode(): ?string
    {
        $dataLocale = $this->requestParams->get('dataLocale', null);
        if (!$dataLocale) {
            $request = $this->requestStack->getCurrentRequest();
            $dataLocale = $request->get('dataLocale', null);
        }
        if (!$dataLocale && $locale = $this->userContext->getUser()->getCatalogLocale()) {
            $dataLocale = $locale->getCode();
        }

        return $dataLocale;
    }

    /**
     * @param string $key the configuration key
     *
     * @return string
     */
    private function getSourcePath($key): string
    {
        return sprintf(self::SOURCE_PATH, $key);
    }
}
