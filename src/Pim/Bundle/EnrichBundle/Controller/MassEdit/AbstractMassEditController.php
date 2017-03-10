<?php

namespace Pim\Bundle\EnrichBundle\Controller\MassEdit;

use Oro\Bundle\DataGridBundle\Extension\MassAction\MassActionParametersParser;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Pim\Bundle\DataGridBundle\Adapter\GridFilterAdapterInterface;
use Pim\Bundle\EnrichBundle\Flash\Message;
use Pim\Bundle\EnrichBundle\MassEditAction\MassEditFormResolver;
use Pim\Bundle\EnrichBundle\MassEditAction\OperationJobLauncher;
use Pim\Bundle\EnrichBundle\MassEditAction\Operation\OperationRegistryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Templating\EngineInterface;
use Symfony\Component\Translation\Exception\NotFoundResourceException;

/**
 * Abstract Mass Edit controller that contains base methods
 * Handle all the steps from choosing action to run to the launching of the action.
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
abstract class AbstractMassEditController
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
     * @return JsonResponse|Response
     */
    public function chooseAction(Request $request, $operationGroup)
    {
        $form = $this
            ->massEditFormResolver
            ->getAvailableOperationsForm($this->getGridName(), $operationGroup);

        $queryParams = $this->getQueryParams($request);

        if ($request->isMethod('POST')) {
            $form->submit($request);

            if ($form->isValid()) {
                $data = $form->getData();
                $queryParams += ['operationAlias' => $data['operationAlias']];
                $queryParams += ['operationGroup' => $operationGroup];

                return new JsonResponse(
                    [
                        'route'  => $this->getChooseOperationRoute(),
                        'params' => $queryParams
                    ]
                );
            }
        }

        $itemsCount = $request->get('itemsCount');

        return $this->templating->renderResponse($this->getChooseOperationTemplate(), [
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
        $configureTemplate = $this->getConfigureOperationTemplate($operationAlias);

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
        $configureTemplate = $this->getPerformOperationTemplate($operationAlias);

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

            $redirectRoute = $this->getPerformOperationRedirectRoute();

            return new JsonResponse(
                [
                    'route'  => $redirectRoute,
                    'params' => ['dataLocale' => $queryParams['dataLocale']]
                ]
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

        $params = array_merge($params, [
            'gridName'       => $request->get('gridName'),
            'actionName'     => $request->get('actionName'),
            'values'         => implode(',', $params['values']),
            'filters'        => json_encode($params['filters']),
            'dataLocale'     => $request->get('dataLocale', null),
            'itemsCount'     => $request->get('itemsCount'),
            'operationGroup' => $request->get('operationGroup')
        ]);

        return $params;
    }

    /**
     * Should return the grid name.
     *
     * @return string
     */
    abstract protected function getGridName();

    /**
     * Should return the choose route
     *
     * @return string
     */
    abstract protected function getChooseOperationRoute();

    /**
     * Should return the configure template
     *
     * @param string $operationAlias
     *
     * @return string
     */
    abstract protected function getConfigureOperationTemplate($operationAlias);

    /**
     * Should return the choose template
     *
     * @return string
     */
    abstract protected function getChooseOperationTemplate();

    /**
     * Should return the perform template
     *
     * @param string $operationAlias
     *
     * @return string
     */
    abstract protected function getPerformOperationTemplate($operationAlias);

    /**
     * Should return the route redirection
     *
     * @return string
     */
    abstract protected function getPerformOperationRedirectRoute();
}
