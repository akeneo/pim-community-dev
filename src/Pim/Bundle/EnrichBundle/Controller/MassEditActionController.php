<?php

namespace Pim\Bundle\EnrichBundle\Controller;

use Akeneo\Bundle\BatchBundle\Connector\ConnectorRegistry;
use Akeneo\Bundle\BatchBundle\Entity\JobInstance;
use Akeneo\Bundle\BatchBundle\Job\DoctrineJobRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\DataGridBundle\Extension\MassAction\MassActionParametersParser;
use Pim\Bundle\DataGridBundle\Adapter\GridFilterAdapterInterface;
use Pim\Bundle\DataGridBundle\Extension\MassAction\MassActionDispatcher;
use Pim\Bundle\EnrichBundle\AbstractController\AbstractDoctrineController;
use Pim\Bundle\EnrichBundle\Form\Type\MassEditOperatorType;
use Pim\Bundle\EnrichBundle\MassEditAction\Manager\MassEditJobManager;
use Pim\Bundle\EnrichBundle\MassEditAction\Operator\AbstractMassEditOperator;
use Pim\Bundle\EnrichBundle\MassEditAction\OperatorRegistry;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
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
    /** @var AbstractMassEditOperator */
    protected $operator;

    /** @var MassActionParametersParser */
    protected $parametersParser;

    /** @var MassActionDispatcher */
    protected $massActionDispatcher;

    /** @var ValidatorInterface */
    protected $validator;

    /** @var array */
    protected $objects;

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
     * @param OperatorRegistry           $operatorRegistry
     * @param MassActionParametersParser $parametersParser
     * @param MassActionDispatcher       $massActionDispatcher
     * @param GridFilterAdapterInterface $gridFilterAdapter
     * @param MassEditJobManager         $massEditJobManager
     * @param DoctrineJobRepository      $jobManager
     * @param ConnectorRegistry          $connectorRegistry
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
        OperatorRegistry $operatorRegistry,
        MassActionParametersParser $parametersParser,
        MassActionDispatcher $massActionDispatcher,
        GridFilterAdapterInterface $gridFilterAdapter,
        MassEditJobManager $massEditJobManager,
        DoctrineJobRepository $jobManager,
        ConnectorRegistry $connectorRegistry
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

        $this->operatorRegistry     = $operatorRegistry;
        $this->parametersParser     = $parametersParser;
        $this->massActionDispatcher = $massActionDispatcher; // TODO: to remove
        $this->gridFilterAdapter    = $gridFilterAdapter;
        $this->massEditJobManager   = $massEditJobManager;
        $this->jobManager           = $jobManager;
        $this->connectorRegistry    = $connectorRegistry;
    }

    /**
     * @Template
     * @AclAncestor("pim_enrich_mass_edit")
     *
     * @return array
     */
    public function chooseAction()
    {
        $operator = $this->operatorRegistry->getOperator(
            $this->request->get('gridName')
        );

        $form = $this->getOperatorForm($operator);

        if ($this->request->isMethod('POST')) {
            $form->submit($this->request);
            if ($form->isValid()) {
                return $this->redirectToRoute(
                    'pim_enrich_mass_edit_action_configure',
                    $this->getQueryParams() + ['operationAlias' => $operator->getOperationAlias()]
                );
            }
        }

        return [
            'form' => $form->createView(),
            'count' => $this->getObjectCount(),
            'queryParams' => $this->getQueryParams(),
            'operator' => $operator,
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
        try {
            $operator = $this->operatorRegistry->getOperator(
                $this->request->get('gridName')
            );

            $operator
                ->setOperationAlias($operationAlias);
        } catch (\InvalidArgumentException $e) {
            throw $this->createNotFoundException($e->getMessage(), $e);
        }

        $operator->initializeOperation();
        $form = $this->getOperatorForm($operator);

        if ($this->request->isMethod('POST')) {
            $form->submit($this->request);
            $operator->initializeOperation();
            $form = $this->getOperatorForm($operator);
        }

        return $this->render(
            sprintf('PimEnrichBundle:MassEditAction:configure/%s.html.twig', $operationAlias),
            [
                'form'         => $form->createView(),
                'operator'     => $operator,
                'productCount' => $this->getObjectCount(),
                'queryParams'  => $this->getQueryParams()
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
        try {
            $operator = $this->operatorRegistry->getOperator(
                $this->request->get('gridName')
            );

            $operator
                ->setOperationAlias($operationAlias);
        } catch (\InvalidArgumentException $e) {
            throw $this->createNotFoundException($e->getMessage(), $e);
        }

        $operator->initializeOperation();
        $form = $this->getOperatorForm($operator, ['Default', 'configureAction']);
        $form->submit($this->request);

        if ($form->isValid()) {

            $operator->getOperation()->saveConfiguration();

            //TODO: to remove !
            $jobInstance = $this->jobManager->getJobManager()->getRepository('AkeneoBatchBundle:JobInstance')->findOneByCode('change_status');
            $jobInstance = $this->getJobInstance($jobInstance->getId());

            $rawConfiguration = json_encode($operator->getOperation()->getConfiguration());
            $this->massEditJobManager->launchJob($jobInstance, $this->getUser(), $rawConfiguration);

            // Binding does not actually perform the operation, thus form errors can miss some constraints
            foreach ($this->validator->validate($operator) as $violation) {
                $form->addError(
                    new FormError(
                        $violation->getMessage(),
                        $violation->getMessageTemplate(),
                        $violation->getMessageParameters(),
                        $violation->getMessagePluralization()
                    )
                );
            }
        }

        if ($form->isValid()) {
            $operator->finalizeOperation();
            $this->addFlash(
                'success',
                sprintf('pim_enrich.mass_edit_action.%s.launched_flash', $operationAlias)
            );

            return $this->redirectToRoute($operator->getPerformedOperationRedirectionRoute());
        }

        return $this->render(
            sprintf('PimEnrichBundle:MassEditAction:configure/%s.html.twig', $operationAlias),
            [
                'form'         => $form->createView(),
                'operator'     => $operator,
                'productCount' => $this->getObjectCount(),
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

    /**
     * @param AbstractMassEditOperator $operator
     * @param array                    $validationGroups
     *
     * @return Form
     */
    protected function getOperatorForm(AbstractMassEditOperator $operator, array $validationGroups = [])
    {
        return $this->createForm(
            new MassEditOperatorType(),
            $operator,
            [
                'operations' => $operator->getOperationChoices(),
                'validation_groups' => $validationGroups
            ]
        );
    }

    /**
     * Get the count of objects to perform the mass action on
     *
     * @return integer
     */
    protected function getObjectCount()
    {
        return count($this->getObjects());
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

    /**
     * Dispatch mass action
     */
    protected function dispatchMassAction()
    {
        $this->objects = $this->massActionDispatcher->dispatch($this->request);
    }

    /**
     * Get products to mass edit
     *
     * @return array
     */
    protected function getObjects()
    {
        if ($this->objects === null) {
            $this->dispatchMassAction();
        }

        return $this->objects;
    }
}
