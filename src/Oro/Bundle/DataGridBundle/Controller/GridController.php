<?php

namespace Oro\Bundle\DataGridBundle\Controller;

use Oro\Bundle\DataGridBundle\Extension\MassAction\MassActionDispatcher;
use Oro\Bundle\DataGridBundle\Extension\MassAction\MassActionParametersParser;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class GridController extends Controller
{
    /**
     * @param string $gridName
     *
     * @return Response
     */
    public function getAction($gridName)
    {
        $grid = $this->get('oro_datagrid.datagrid.manager')->getDatagrid($gridName);
        $result = $grid->getData();

        return new JsonResponse($result->toArray());
    }

    /**
     * @param string $gridName
     * @param string $actionName
     *
     * @throws \LogicException
     * @return Response
     */
    public function massActionAction(Request $request, $gridName, $actionName)
    {
        /** @var MassActionParametersParser $massActionParametersParser */
        $parametersParser = $this->get('oro_datagrid.mass_action.parameters_parser');
        $parameters = $parametersParser->parse($request);

        $requestData = array_merge($request->query->all(), $request->request->all());

        /** @var MassActionDispatcher $massActionDispatcher */
        $massActionDispatcher = $this->get('oro_datagrid.mass_action.dispatcher');
        $response = $massActionDispatcher->dispatch($gridName, $actionName, $parameters, $requestData);

        $data = [
            'successful' => $response->isSuccessful(),
            'message'    => $response->getMessage(),
        ];

        return new JsonResponse(array_merge($data, $response->getOptions()));
    }
}
