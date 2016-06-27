<?php

namespace Pim\Bundle\EnrichBundle\Controller\MassEdit;

use Oro\Bundle\DataGridBundle\Extension\MassAction\MassActionParametersParser;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Pim\Bundle\DataGridBundle\Adapter\GridFilterAdapterInterface;
use Pim\Bundle\EnrichBundle\Flash\Message;
use Pim\Bundle\EnrichBundle\MassEditAction\MassEditFormResolver;
use Pim\Bundle\EnrichBundle\MassEditAction\Operation\OperationRegistryInterface;
use Pim\Bundle\EnrichBundle\MassEditAction\OperationJobLauncher;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Templating\EngineInterface;
use Symfony\Component\Translation\Exception\NotFoundResourceException;

/**
 * Mass Edit controller implementation for products.
 * Handle all the steps from choosing action to run to the launching of the action.
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ProductController
{
    /** @var RouterInterface */
    protected $router;

    /** @var EngineInterface */
    protected $templating;

    /** @var MassActionParametersParser */
    protected $parametersParser;

    /** @var GridFilterAdapterInterface */
    protected $gridFilterAdapter;

    /** @var MassEditFormResolver */
    protected $massEditFormResolver;

    /** @var OperationRegistryInterface */
    protected $operationRegistry;

    /** @var OperationJobLauncher */
    protected $operationLauncher;

    /**
     * @param RouterInterface            $router
     * @param EngineInterface            $templating
     * @param MassActionParametersParser $parametersParser
     * @param GridFilterAdapterInterface $gridFilterAdapter
     * @param MassEditFormResolver       $massEditFormResolver
     * @param OperationRegistryInterface $operationRegistry
     * @param OperationJobLauncher       $operationLauncher
     */
    public function __construct(
        RouterInterface $router,
        EngineInterface $templating,
        MassActionParametersParser $parametersParser,
        GridFilterAdapterInterface $gridFilterAdapter,
        MassEditFormResolver $massEditFormResolver,
        OperationRegistryInterface $operationRegistry,
        OperationJobLauncher $operationLauncher
    ) {
        $this->router = $router;
        $this->templating = $templating;
        $this->parametersParser = $parametersParser;
        $this->gridFilterAdapter = $gridFilterAdapter;
        $this->massEditFormResolver = $massEditFormResolver;
        $this->operationRegistry = $operationRegistry;
        $this->operationLauncher = $operationLauncher;
    }

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
            ->massEditFormResolver
            ->getAvailableOperationsForm('product-grid', $operationGroup);

        $queryParams = $this->getQueryParams($request);

        if ($request->isMethod('POST')) {
            $form->submit($request);

            if ($form->isValid()) {
                $data = $form->getData();
                $queryParams += ['operationAlias' => $data['operationAlias']];

                $configureRoute = $this
                    ->router
                    ->generate('pim_enrich_mass_edit_product_action_configure', $queryParams);

                return new RedirectResponse($configureRoute);
            }
        }

        $itemsCount = $request->get('itemsCount');

        return $this->templating->renderResponse('PimEnrichBundle:MassEditAction:product/choose.html.twig', [
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
            ->operationRegistry
            ->get($operationAlias);

        $form = $this
            ->massEditFormResolver
            ->getConfigurationForm($operationAlias);

        $itemsCount = $request->get('itemsCount');
        $configureTemplate = sprintf('PimEnrichBundle:MassEditAction:product/configure/%s.html.twig', $operationAlias);

        return $this->templating->renderResponse(
            $configureTemplate,
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
            ->massEditFormResolver
            ->getConfigurationForm($operationAlias);

        $itemsCount = $request->get('itemsCount');
        $configureTemplate = sprintf('MassEditAction/configure/%s.html.twig', $operationAlias);

        $form->remove('operationAlias');
        $form->submit($request);

        $queryParams = $this->getQueryParams($request);

        if ($form->isValid()) {
            $pimFilters = $this
                ->gridFilterAdapter
                ->adapt($request);

            $operation = $form->getData();
            $operation->setFilters($pimFilters);

            $this
                ->operationLauncher
                ->launch($operation);

            $request
                ->getSession()
                ->getFlashBag()
                ->add(
                    'success',
                    new Message(sprintf('pim_enrich.mass_edit_action.%s.launched_flash', $operationAlias))
                );

            $redirectRoute = 'pim_enrich_product_index';

            return new RedirectResponse(
                $this->router->generate($redirectRoute, ['dataLocale' => $queryParams['dataLocale']])
            );
        }

        return $this->templating->renderResponse(
            $configureTemplate,
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
            ->parametersParser
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
