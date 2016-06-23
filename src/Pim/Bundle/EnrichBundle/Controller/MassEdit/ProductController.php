<?php

namespace Pim\Bundle\EnrichBundle\Controller\MassEdit;

use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Pim\Bundle\EnrichBundle\Flash\Message;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Translation\Exception\NotFoundResourceException;

/**
 * Mass Edit controller implementation for products.
 * Handle all the steps from choosing action to run to the launching of the action.
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ProductController extends Controller
{
    /**
     * Display the form to choose the mass edit action to execute.
     *
     * @AclAncestor("pim_enrich_mass_edit")
     *
     * @param Request $request
     * @param string  $operationGroup
     *
     * @return RedirectResponse|Response
     */
    public function chooseAction(Request $request, $operationGroup)
    {
        $form = $this
            ->get('pim_enrich.mass_edit_action.form_resolver')
            ->getAvailableOperationsForm('product-grid', $operationGroup);

        $queryParams = $this->getQueryParams($request);

        if ($request->isMethod('POST')) {
            $form->submit($request);

            if ($form->isValid()) {
                $data = $form->getData();
                $queryParams += ['operationAlias' => $data['operationAlias']];

                $configureRoute = $this
                    ->get('router')
                    ->generate('pim_enrich_mass_edit_action_configure', $queryParams);

                return new RedirectResponse($configureRoute);
            }
        }

        $itemsCount = $request->get('itemsCount');

        return $this->render('PimEnrichBundle:ProductMassEditAction:choose.html.twig', [
            'form'        => $form->createView(),
            'itemsCount'  => $itemsCount,
            'queryParams' => array_merge($queryParams, ['operationGroup' => $operationGroup]),
        ]);
    }

    /**
     * Display the form to configure the mass edit action to execute
     *
     * @AclAncestor("pim_enrich_mass_edit")
     *
     * @param Request $request
     * @param string  $operationAlias
     *
     * @return Response
     */
    public function configureAction(Request $request, $operationAlias)
    {
        $operation = $this
            ->get('pim_enrich.mass_edit_action.operation.registry')
            ->get($operationAlias);

        $form = $this
            ->get('pim_enrich.mass_edit_action.form_resolver')
            ->getConfigurationForm($operationAlias);

        $itemsCount = $request->get('itemsCount');
        $configureRoute = sprintf('PimEnrichBundle:ProductMassEditAction/configure:%s.html.twig', $operationAlias);

        return $this->render($configureRoute,
            [
                'form'           => $form->createView(),
                'operationAlias' => $operationAlias,
                'operation'      => $operation,
                'queryParams'    => $this->getQueryParams($request),
                'itemsCount'     => $itemsCount,
            ]
        );
    }

    /**
     * Launch the background process related to the mass edit action
     *
     * @AclAncestor("pim_enrich_mass_edit")
     *
     * @param Request $request
     * @param string  $operationAlias
     *
     * @throws NotFoundResourceException
     *
     * @return Response
     */
    public function performAction(Request $request, $operationAlias)
    {
        $form = $this
            ->get('pim_enrich.mass_edit_action.form_resolver')
            ->getConfigurationForm($operationAlias);

        $itemsCount = $request->get('itemsCount');
        $configureRoute = sprintf('MassEditAction/configure/%s.html.twig', $operationAlias);

        $form->remove('operationAlias');
        $form->submit($request);

        $queryParams = $this->getQueryParams($request);

        if ($form->isValid()) {
            $pimFilters = $this
                ->get('pim_datagrid.adapter.oro_to_pim_grid_filter')
                ->adapt($request);

            $operation = $form->getData();
            $operation->setFilters($pimFilters);

            $this
                ->get('pim_enrich.mass_edit_action.operation_job_launcher')
                ->launch($operation);

            $this->addFlash(
                'success',
                new Message(sprintf('pim_enrich.mass_edit_action.%s.launched_flash', $operationAlias))
            );

            $route = 'pim_enrich_product_index';

            return new RedirectResponse(
                $this->get('router')->generate($route, ['dataLocale' => $queryParams['dataLocale']])
            );
        }

        return $this->render($configureRoute,
            [
                'form'           => $form->createView(),
                'operationAlias' => $operationAlias,
                'itemsCount'     => $itemsCount,
                'queryParams'    => $queryParams
            ]
        );
    }

    /**
     * Get the datagrid query parameters
     *
     * @param Request $request
     *
     * @return array
     */
    protected function getQueryParams(Request $request)
    {
        $params = $this
            ->get('oro_datagrid.mass_action.parameters_parser')
            ->parse($request);

        $params['gridName']   = $request->get('gridName');
        $params['actionName'] = $request->get('actionName');
        $params['values']     = implode(',', $params['values']);
        $params['filters']    = json_encode($params['filters']);
        $params['dataLocale'] = $request->get('dataLocale', null);
        $params['itemsCount'] = $request->get('itemsCount');

        return $params;
    }
}
