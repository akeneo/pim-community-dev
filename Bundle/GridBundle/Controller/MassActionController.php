<?php

namespace Oro\Bundle\GridBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use Oro\Bundle\UserBundle\Annotation\Acl;
use Oro\Bundle\GridBundle\Action\MassAction\MassActionDispatcher;

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
        // get parameters
        $inset = $this->getRequest()->get('inset', true);
        $inset = !empty($inset);

        $values = $this->getRequest()->get('values', '');
        if (!is_array($values)) {
            $values = $values !== '' ? explode(',', $values) : array();
        }

        $filters = $this->getRequest()->get('filters', array());

        /** @var MassActionDispatcher $massActionDispatcher */
        $massActionDispatcher = $this->get('oro_grid.mass_action.dispatcher');
        $response = $massActionDispatcher->dispatch($gridName, $actionName, $inset, $values, $filters);

        $data = array(
            'successful' => $response->isSuccessful(),
            'message'    => $response->getMessage(),
        );

        return new JsonResponse(array_merge($data, $response->getOptions()));
    }
}
