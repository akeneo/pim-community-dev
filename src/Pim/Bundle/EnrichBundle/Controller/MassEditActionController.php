<?php

namespace Pim\Bundle\EnrichBundle\Controller;

use Akeneo\Bundle\BatchBundle\Connector\ConnectorRegistry;
use Akeneo\Bundle\BatchBundle\Entity\JobInstance;
use Akeneo\Bundle\BatchBundle\Job\DoctrineJobRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\DataGridBundle\Extension\MassAction\MassActionParametersParser;
use Pim\Bundle\DataGridBundle\Adapter\GridFilterAdapterInterface;
use Pim\Bundle\EnrichBundle\AbstractController\AbstractDoctrineController;
use Pim\Bundle\EnrichBundle\Form\Type\MassEditOperatorType;
use Pim\Bundle\EnrichBundle\MassEditAction\Manager\MassEditJobManager;
use Pim\Bundle\EnrichBundle\MassEditAction\OperationRegistry;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\ValidatorInterface;

/**
 * Mass edit operation controller
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MassEditActionController extends AbstractDoctrineController
{
    /** @var MassActionParametersParser */
    protected $parametersParser;

    /** @var ValidatorInterface */
    protected $validator;

    /** @var GridFilterAdapterInterface */
    protected $gridFilterAdapter;

    /** @var MassEditJobManager */
    protected $massEditJobManager;

    /** @var DoctrineJobRepository */
    protected $jobManager;

    /** @var ConnectorRegistry */
    protected $connectorRegistry;

    /**
     * Constructor
     *
     * @param Request                    $request
     * @param EngineInterface            $templating
     * @param RouterInterface            $router
     * @param SecurityContextInterface   $securityContext
     * @param FormFactoryInterface       $formFactory
     * @param ValidatorInterface         $validator
     * @param TranslatorInterface        $translator
     * @param EventDispatcherInterface   $eventDispatcher
     * @param ManagerRegistry            $doctrine
     * @param MassActionParametersParser $parametersParser
     * @param GridFilterAdapterInterface $gridFilterAdapter
     * @param MassEditJobManager         $massEditJobManager
     * @param DoctrineJobRepository      $jobManager
     * @param ConnectorRegistry          $connectorRegistry
     * @param OperationRegistry          $operationRegistry
     */
    public function __construct(
        Request $request,
        EngineInterface $templating,
        RouterInterface $router,
        SecurityContextInterface $securityContext,
        FormFactoryInterface $formFactory,
        ValidatorInterface $validator,
        TranslatorInterface $translator,
        EventDispatcherInterface $eventDispatcher,
        ManagerRegistry $doctrine,
        MassActionParametersParser $parametersParser,
        GridFilterAdapterInterface $gridFilterAdapter,
        MassEditJobManager $massEditJobManager,
        DoctrineJobRepository $jobManager,
        ConnectorRegistry $connectorRegistry,
        OperationRegistry $operationRegistry
    ) {
        parent::__construct(
            $request,
            $templating,
            $router,
            $securityContext,
            $formFactory,
            $validator,
            $translator,
            $eventDispatcher,
            $doctrine
        );

        $this->parametersParser   = $parametersParser;
        $this->gridFilterAdapter  = $gridFilterAdapter;
        $this->massEditJobManager = $massEditJobManager;
        $this->jobManager         = $jobManager;
        $this->connectorRegistry  = $connectorRegistry;
        $this->operationRegistry  = $operationRegistry;
    }

    /**
     * @Template
     * @AclAncestor("pim_enrich_mass_edit")
     *
     * @return array
     */
    public function chooseAction()
    {
        $gridName = $this->request->get('gridName');
        $itemsName = $this->getItemsName($gridName);

        $availableOperations = $this->operationRegistry->getAllByGridName($gridName);
        $form = $this->getOperationsForm($availableOperations);

        if ($this->request->isMethod('POST')) {
            $form->submit($this->request);
            if ($form->isValid()) {
                $data = $form->getData();
                return $this->redirectToRoute(
                    'pim_enrich_mass_edit_action_configure',
                    $this->getQueryParams() + ['operationAlias' => $data['operationAlias']]
                );
            }
        }

        return [
            'form'        => $form->createView(),
            'queryParams' => $this->getQueryParams(),
            'itemsName'   => $itemsName
        ];
    }

    /**
     * @param string $operationAlias
     *
     * @AclAncestor("pim_enrich_mass_edit")
     * @throws NotFoundHttpException
     * @return Response|RedirectResponse
     */
    public function configureAction($operationAlias)
    {
        $operation = $this->operationRegistry->get($operationAlias);
        $itemsName = $operation->getItemsName();

        $form = $this->createForm(new MassEditOperatorType());
        $form->add('operation', $operation->getFormType(), $operation->getFormOptions());

        if ($this->request->isMethod('POST')) {
            $form->submit($this->request);
        }

        return $this->render(
            sprintf('PimEnrichBundle:MassEditAction:configure/%s.html.twig', $operationAlias),
            [
                'form'           => $form->createView(),
                'operationAlias' => $operationAlias,
                'queryParams'    => $this->getQueryParams(),
                'itemsName'      => $itemsName
            ]
        );
    }

    /**
     * @param string $operationAlias
     *
     * @AclAncestor("pim_enrich_mass_edit")
     * @throws NotFoundHttpException
     * @return Response|RedirectResponse
     */
    public function performAction($operationAlias)
    {
        $operation = $this->operationRegistry->get($operationAlias);
        $itemsName = $operation->getItemsName();

        $form = $this->createForm(new MassEditOperatorType());
        $form->add('operation', $operation->getFormType(), $operation->getFormOptions());
        $form->submit($this->request);

        if ($form->isValid()) {

            $operation = $form->getData()['operation'];
            $pimFilters = $this->gridFilterAdapter->transform($this->request);
            $operation->setFilters($pimFilters);

            $rawConfiguration = $operation->getBatchConfig();
            //TODO: to remove !
            $jobCode = $operation->getBatchJobCode();
            $jobInstance = $this->jobManager->getJobManager()
                ->getRepository('AkeneoBatchBundle:JobInstance')
                ->findOneByCode($jobCode);
            $jobInstance = $this->getJobInstance($jobInstance->getId());

            $this->massEditJobManager->launchJob($jobInstance, $this->getUser(), $rawConfiguration);

            // Binding does not actually perform the operation, thus form errors can miss some constraints
//            foreach ($this->validator->validate($operator) as $violation) {
//                $form->addError(
//                    new FormError(
//                        $violation->getMessage(),
//                        $violation->getMessageTemplate(),
//                        $violation->getMessageParameters(),
//                        $violation->getMessagePluralization()
//                    )
//                );
//            }
        }

        if ($form->isValid()) {
            $this->addFlash(
                'success',
                sprintf('pim_enrich.mass_edit_action.%s.launched_flash', $operationAlias)
            );

            return $this->redirectToRoute('pim_enrich_product_index');
        }

        return $this->render(
            sprintf('PimEnrichBundle:MassEditAction:configure/%s.html.twig', $operationAlias),
            [
                'form'         => $form->createView(),
                'operationAlias' => $operationAlias,
                'itemsName'      => $itemsName,
                'queryParams'  => $this->getQueryParams()
            ]
        );
    }

    /**
     * Get a job instance
     *
     * @param integer $id
     * @param boolean $checkStatus
     *
     * @return Job|RedirectResponse
     *
     * @throws NotFoundHttpException
     */
    protected function getJobInstance($id, $checkStatus = true)
    {
        $jobInstance = $this->findOr404('AkeneoBatchBundle:JobInstance', $id);

        // Fixme: should look at the job execution to see the status of a job instance execution
        if ($checkStatus && $jobInstance->getStatus() === JobInstance::STATUS_IN_PROGRESS) {
            throw $this->createNotFoundException(
                sprintf('The %s "%s" is currently in progress', $jobInstance->getType(), $jobInstance->getLabel())
            );
        }

        $job = $this->connectorRegistry->getJob($jobInstance);

        if (!$job) {
            throw $this->createNotFoundException(
                sprintf(
                    'The following %s does not exist anymore. Please check configuration:<br />' .
                    'Connector: %s<br />' .
                    'Type: %s<br />' .
                    'Alias: %s',
                    $this->getJobType(),
                    $jobInstance->getConnector(),
                    $jobInstance->getType(),
                    $jobInstance->getAlias()
                )
            );
        }
        $jobInstance->setJob($job);

        return $jobInstance;
    }

    protected function getItemsName($gridName)
    {
        switch ($gridName) {
            case 'product-grid':
                return 'product';
            case 'family-grid':
                return 'family';
            default:
                return 'item';
        }
    }

    protected function getOperationsForm($availableOperations)
    {
        $choices = [];

        foreach (array_keys($availableOperations) as $alias) {
                $choices[$alias] = sprintf('pim_enrich.mass_edit_action.%s.label', $alias);
        }

        return $this->createForm(
            new MassEditOperatorType(),
            null,
            [
                'operations' => $choices
            ]
        );
    }

    /**
     * Get the datagrid query parameters
     *
     * @return array
     */
    protected function getQueryParams()
    {
        $params = $this->parametersParser->parse($this->request);

        $params['gridName']   = $this->request->get('gridName');
        $params['actionName'] = $this->request->get('actionName');
        $params['values']     = implode(',', $params['values']);
        $params['filters']    = json_encode($params['filters']);
        $params['dataLocale'] = $this->request->get('dataLocale', null);

        return $params;
    }
}
