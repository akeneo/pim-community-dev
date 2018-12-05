<?php

namespace Akeneo\Pim\Enrichment\Bundle\Controller\InternalApi;

use Akeneo\Pim\Enrichment\Bundle\MassEditAction\Operation\MassEditOperation;
use Akeneo\Pim\Enrichment\Bundle\MassEditAction\OperationJobLauncher;
use Akeneo\Pim\Enrichment\Component\Product\Converter\ConverterInterface;
use Oro\Bundle\DataGridBundle\Extension\MassAction\MassActionParametersParser;
use Oro\Bundle\PimDataGridBundle\Adapter\GridFilterAdapterInterface;
use Oro\Bundle\PimDataGridBundle\Adapter\ItemsCounter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Mass edit controller
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MassEditController
{
    /** @var MassActionParametersParser */
    protected $parameterParser;

    /** @var GridFilterAdapterInterface */
    protected $filterAdapter;

    /** @var OperationJobLauncher */
    protected $operationJobLauncher;

    /** @var ConverterInterface */
    protected $operationConverter;

    /** @var ItemsCounter */
    protected $itemsCounter;

    /**
     * @param MassActionParametersParser $parameterParser
     * @param GridFilterAdapterInterface $filterAdapter
     * @param OperationJobLauncher       $operationJobLauncher
     * @param ConverterInterface         $operationConverter
     * @param ItemsCounter               $itemsCounter
     */
    public function __construct(
        MassActionParametersParser $parameterParser,
        GridFilterAdapterInterface $filterAdapter,
        OperationJobLauncher $operationJobLauncher,
        ConverterInterface $operationConverter,
        ItemsCounter $itemsCounter
    ) {
        $this->parameterParser      = $parameterParser;
        $this->filterAdapter        = $filterAdapter;
        $this->operationJobLauncher = $operationJobLauncher;
        $this->operationConverter   = $operationConverter;
        $this->itemsCounter = $itemsCounter;
    }

    /**
     * Get filters from datagrid request
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function getFilterAction(Request $request)
    {
        $parameters = $this->parameterParser->parse($request);
        $filters = $this->filterAdapter->adapt($parameters);
        $itemsCount = $this->itemsCounter->count($parameters['gridName'], $filters);

        return new JsonResponse(
            [
                'filters'    => $filters,
                'itemsCount' => $itemsCount
            ]
        );
    }

    /**
     * Launch mass edit action
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function launchAction(Request $request)
    {
        $data = json_decode($request->getContent(), true);

        $data = $this->operationConverter->convert($data);
        $operation = new MassEditOperation($data['jobInstanceCode'], $data['filters'], $data['actions']);
        $this->operationJobLauncher->launch($operation);

        return new JsonResponse();
    }
}
