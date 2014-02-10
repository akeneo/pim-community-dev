<?php

namespace Pim\Bundle\EnrichBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Validator\ValidatorInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Form\FormError;

use Doctrine\ORM\AbstractQuery;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\DataGridBundle\Extension\MassAction\MassActionParametersParser;
use Oro\Bundle\DataGridBundle\Extension\MassAction\MassActionDispatcher;

use Pim\Bundle\EnrichBundle\Form\Type\MassEditActionOperatorType;
use Pim\Bundle\EnrichBundle\AbstractController\AbstractDoctrineController;
use Pim\Bundle\EnrichBundle\MassEditAction\MassEditActionOperator;

/**
 * Mass edit operation controller
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MassEditActionController extends AbstractDoctrineController
{
    /** @var MassEditActionOperator */
    protected $operator;

    /** @var MassActionParametersParser */
    protected $parametersParser;

    /** @var MassActionDispatcher */
    protected $massActionDispatcher;

    /** @var ValidatorInterface */
    protected $validator;

    /** @var \Doctrine\ORM\QueryBuilder */
    protected $gridQB;

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
     * @param RegistryInterface          $doctrine
     * @param MassEditActionOperator     $operator
     * @param MassActionParametersParser $parametersParser
     * @param MassActionDispatcher       $massActionDispatcher
     */
    public function __construct(
        Request $request,
        EngineInterface $templating,
        RouterInterface $router,
        SecurityContextInterface $securityContext,
        FormFactoryInterface $formFactory,
        ValidatorInterface $validator,
        TranslatorInterface $translator,
        RegistryInterface $doctrine,
        MassEditActionOperator $operator,
        MassActionParametersParser $parametersParser,
        MassActionDispatcher $massActionDispatcher
    ) {
        parent::__construct(
            $request,
            $templating,
            $router,
            $securityContext,
            $formFactory,
            $validator,
            $translator,
            $doctrine
        );

        $this->validator            = $validator;
        $this->operator             = $operator;
        $this->parametersParser     = $parametersParser;
        $this->massActionDispatcher = $massActionDispatcher;
    }

    /**
     * @param Request $request
     *
     * @Template
     * @AclAncestor("pim_enrich_mass_edit")
     * @return template|RedirectResponse
     */
    public function chooseAction(Request $request)
    {
        $productCount = $this->getProductCount($request);
        if (!$productCount) {
            return $this->redirectToRoute('pim_enrich_product_index');
        }

        $form = $this->getOperatorForm();

        if ($request->isMethod('POST')) {
            $form->submit($request);
            if ($form->isValid()) {
                return $this->redirectToRoute(
                    'pim_enrich_mass_edit_action_configure',
                    $this->getQueryParams($request) + ['operationAlias' => $this->operator->getOperationAlias()]
                );
            }
        }

        return array(
            'form'         => $form->createView(),
            'productCount' => $productCount,
            'queryParams'  => $this->getQueryParams($request)
        );
    }

    /**
     * @param Request $request
     * @param string  $operationAlias
     *
     * @AclAncestor("pim_enrich_mass_edit")
     * @throws NotFoundHttpException
     * @return template|RedirectResponse
     */
    public function configureAction(Request $request, $operationAlias)
    {
        try {
            $this->operator->setOperationAlias($operationAlias);
        } catch (\InvalidArgumentException $e) {
            throw $this->createNotFoundException($e->getMessage(), $e);
        }

        $productCount = $this->getProductCount($request);
        if (!$productCount) {
            return $this->redirectToRoute('pim_enrich_product_index');
        }

        $this->operator->initializeOperation($this->getGridQB($request));
        $form = $this->getOperatorForm();

        if ($request->isMethod('POST')) {
            $form->submit($request);
            $this->operator->initializeOperation($this->getGridQB($request));
            $form = $this->getOperatorForm();
        }

        return $this->render(
            sprintf('PimEnrichBundle:MassEditAction:configure/%s.html.twig', $operationAlias),
            array(
                'form'         => $form->createView(),
                'operator'     => $this->operator,
                'productCount' => $productCount,
                'queryParams'  => $this->getQueryParams($request)
            )
        );
    }

    /**
     * @param Request $request
     * @param string  $operationAlias
     *
     * @AclAncestor("pim_enrich_mass_edit")
     * @throws NotFoundHttpException
     * @return template|RedirectResponse
     */
    public function performAction(Request $request, $operationAlias)
    {
        try {
            $this->operator->setOperationAlias($operationAlias);
        } catch (\InvalidArgumentException $e) {
            throw $this->createNotFoundException($e->getMessage(), $e);
        }

        $productCount = $this->getProductCount($request);
        if (!$productCount) {
            return $this->redirectToRoute('pim_enrich_product_index');
        }

        $this->operator->initializeOperation($this->getGridQB($request));
        $form = $this->getOperatorForm();
        $form->submit($request);

        // Binding does not actually perform the operation, thus form errors can miss some constraints
        $this->operator->performOperation($this->getGridQB($request));
        foreach ($this->validator->validate($this->operator) as $violation) {
            $form->addError(
                new FormError(
                    $violation->getMessage(),
                    $violation->getMessageTemplate(),
                    $violation->getMessageParameters(),
                    $violation->getMessagePluralization()
                )
            );
        }

        if ($form->isValid()) {
            $this->operator->finalizeOperation($this->getGridQB($request));
            $this->addFlash(
                'success',
                sprintf('pim_enrich.mass_edit_action.%s.success_flash', $operationAlias)
            );

            return $this->redirectToRoute('pim_enrich_product_index');
        }

        return $this->render(
            sprintf('PimEnrichBundle:MassEditAction:configure/%s.html.twig', $operationAlias),
            array(
                'form'         => $form->createView(),
                'operator'     => $this->operator,
                'productCount' => $productCount,
                'queryParams'  => $this->getQueryParams($request)
            )
        );
    }

    /**
     * @return Form
     */
    protected function getOperatorForm()
    {
        return $this->createForm(
            new MassEditActionOperatorType(),
            $this->operator,
            array('operations' => $this->operator->getOperationChoices())
        );
    }

    /**
     * Get the count of products to perform the mass action on
     *
     * @param Request $request
     *
     * @return integer
     */
    protected function getProductCount(Request $request)
    {
        $qb = clone $this->getGridQB($request);

        $rootAlias = $qb->getRootAlias();
        $qb->resetDQLPart('select');
        $qb->select(sprintf('COUNT (%s.id)', $rootAlias));

        return (int) $qb->getQuery()->getResult(AbstractQuery::HYDRATE_SINGLE_SCALAR);
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
        $params = $this->parametersParser->parse($request);

        $params['gridName'] = $request->get('gridName');
        $params['values']   = implode(',', $params['values']);
        $params['filters']  = json_encode($params['filters']);

        return $params;
    }

    /**
     * Get the query builder with grid parameters applied
     *
     * @param Request $request
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function getGridQB(Request $request)
    {
        if (null === $this->gridQB) {
            $parameters  = $this->parametersParser->parse($request);
            $requestData = array_merge($request->query->all(), $request->request->all());

            $qb = $this->massActionDispatcher->dispatch(
                $requestData['gridName'],
                'export',
                $parameters,
                $requestData
            );

            $rootAlias = $qb->getRootAlias();
            $qb->resetDQLPart('select');
            $qb->select($rootAlias);

            $from = current($qb->getDQLPart('from'));
            $qb->resetDQLPart('from');
            $qb->from($from->getFrom(), $from->getAlias());

            $this->gridQB = $qb;
        }

        return $this->gridQB;
    }
}
