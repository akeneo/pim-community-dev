<?php

namespace Oro\Bundle\PimDataGridBundle\EventListener;

use Akeneo\Pim\Enrichment\Bundle\Resolver\FQCNResolver;
use Oro\Bundle\DataGridBundle\Datagrid\RequestParameters;
use Oro\Bundle\DataGridBundle\Event\BuildBefore;
use Oro\Bundle\PimDataGridBundle\Datagrid\Configuration\Product\ContextConfigurator;
use Symfony\Component\HttpFoundation\Request;

/**
 * Listener to configure history grids.
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ConfigureHistoryGridListener
{
    /** @staticvar string */
    const GRID_PARAM_CLASS = 'object_class';

    /** @staticvar string */
    const GRID_PARAM_OBJECT_ID = 'object_id';

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var RequestParameters
     */
    protected $requestParams;

    /**
     * @var FQCNResolver
     */
    protected $FQCNResolver;

    /**
     * @param RequestParameters $requestParams
     * @param FQCNResolver      $FQCNResolver
     */
    public function __construct(RequestParameters $requestParams, FQCNResolver $FQCNResolver)
    {
        $this->requestParams = $requestParams;
        $this->FQCNResolver = $FQCNResolver;
    }

    /**
     * @param BuildBefore $event
     */
    public function onBuildBefore(BuildBefore $event)
    {
        $config = $event->getConfig();
        $objectClassParameter = $this->getObjectClassParameter();
        $objectClass = $this->getObjectClass($objectClassParameter);

        $repositoryParameters = [
            'objectClass' => str_replace(
                '_',
                '\\',
                $objectClass
            ),
            'objectId' => $this->requestParams->get(self::GRID_PARAM_OBJECT_ID, 0),
        ];

        $config->offsetSetByPath(
            sprintf(ContextConfigurator::SOURCE_PATH, ContextConfigurator::REPOSITORY_PARAMETERS_KEY),
            $repositoryParameters
        );
    }

    /**
     * Get the object class parameter from the request.
     * It can be an empty string, a entity type (eg product, group, attribute) or an FQCN
     *
     * @return string
     */
    protected function getObjectClassParameter()
    {
        return $this->requestParams->get(self::GRID_PARAM_CLASS, '');
    }

    /**
     * Convert the object class parameter to a FQCN
     *
     * @param string $objectClassParameter
     *
     * @return string
     */
    protected function getObjectClass($objectClassParameter)
    {
        if ('' === $objectClassParameter || null === $this->FQCNResolver->getFQCN($objectClassParameter)) {
            return $objectClassParameter;
        }

        return $this->FQCNResolver->getFQCN($objectClassParameter);
    }
}
