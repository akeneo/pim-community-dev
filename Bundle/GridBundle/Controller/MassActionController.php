<?php

namespace Oro\Bundle\GridBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use Oro\Bundle\UserBundle\Annotation\Acl;
use Oro\Bundle\GridBundle\Action\MassAction\MassActionDispatcher;
use Oro\Bundle\GridBundle\Action\MassAction\MassActionParametersParser;

/**
 * @Acl(
 *      id="oro_grid",
 *      name="Grid manipulation",
 *      description="Grid manipulation"
 * )
 */
class MassActionController extends Controller
{
    /**
     * @Route("/{gridName}/massAction/{actionName}", name="oro_grid_mass_action")
     * @Acl(
     *      id="oro_grid_mass_action",
     *      name="Datagrid mass action",
     *      description="Datagrid mass action entry point",
     *      parent="oro_grid"
     * )
     * @param string $gridName
     * @param string $actionName
     * @return Response
     * @throws \LogicException
     */
    public function massActionAction($gridName, $actionName)
    {
        $request = $this->getRequest();

        /** @var MassActionParametersParser $massActionParametersParser */
        $parametersParser = $this->get('oro_grid.mass_action.parameters_parser');
        $parameters = $parametersParser->parse($request);

        $requestData = array_merge($request->query->all(), $request->request->all());

        /** @var MassActionDispatcher $massActionDispatcher */
        $massActionDispatcher = $this->get('oro_grid.mass_action.dispatcher');
        $response = $massActionDispatcher->dispatch($gridName, $actionName, $parameters, $requestData);

        $data = array(
            'successful' => $response->isSuccessful(),
            'message'    => $response->getMessage(),
        );

        return new JsonResponse(array_merge($data, $response->getOptions()));
    }
}
