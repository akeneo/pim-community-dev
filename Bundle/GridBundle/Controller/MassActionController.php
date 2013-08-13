<?php

namespace Oro\Bundle\GridBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use Oro\Bundle\UserBundle\Autocomplete\UserSearchHandler;

use Oro\Bundle\UserBundle\Annotation\Acl;
use Oro\Bundle\GridBundle\DependencyInjection\Compiler\AddDependencyCallsCompilerPass;
use Oro\Bundle\GridBundle\Datagrid\DatagridManagerRegistry;
use Oro\Bundle\GridBundle\Datagrid\DatagridManagerInterface;

use Symfony\Component\HttpFoundation\Response;

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
     * @param string $name
     * @return DatagridManagerInterface
     */
    protected function getDatagridManagerByName($name)
    {
        /** @var DatagridManagerRegistry $managerRegistry */
        $managerRegistry = $this->get(AddDependencyCallsCompilerPass::REGISTRY_SERVICE);

        return $managerRegistry->getDatagridManager($name);
    }

    /**
     * @Route("/{gridName}/massAction/{actionName}", name="oro_grid_mass_action")
     * @Acl(
     *      id="oro_grid_mass_action",
     *      name="Datagrid mass action",
     *      description="Datagrid mass action entry point",
     *      parent="oro_grid"
     *
     * @param string $gridName
     * @param string $actionName
     * @return Response
     * @throws \LogicException
     */
    public function massActionAction($gridName, $actionName)
    {
        $datagridManager = $this->getDatagridManagerByName($gridName);

        // get parameters
        $inset = $this->getRequest()->get('inset', true);
        $inset = !empty($inset);

        $values = $this->getRequest()->get('values', '');
        $values = $values !== '' ? explode(',', $values) : array();

        // if there is nothing to do
        if ($inset && empty($values)) {
            throw new \LogicException(sprintf('There is nothing to do in mass action "%s"', $actionName));
        }

        return new Response(
            sprintf(
                'grid=%s action=%s inset=%s values=[%s]',
                $gridName,
                $actionName,
                $inset ? 'true' : 'false',
                implode(', ', $values)
            )
        );
    }
}
