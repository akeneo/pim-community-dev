<?php

namespace Pim\Bundle\EnrichBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Validator\ValidatorInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Form\FormError;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\Query\Expr\From;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\DataGridBundle\Extension\MassAction\MassActionParametersParser;

use Pim\Bundle\DataGridBundle\Extension\MassAction\MassActionDispatcher;
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

    /** @var integer */
    protected $massEditLimit;

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
     * @param ManagerRegistry            $doctrine
     * @param MassEditActionOperator     $operator
     * @param MassActionParametersParser $parametersParser
     * @param MassActionDispatcher       $massActionDispatcher
     * @param integer                    $massEditLimit
     */
    public function __construct(
        Request $request,
        EngineInterface $templating,
        RouterInterface $router,
        SecurityContextInterface $securityContext,
        FormFactoryInterface $formFactory,
        ValidatorInterface $validator,
        TranslatorInterface $translator,
        ManagerRegistry $doctrine,
        MassEditActionOperator $operator,
        MassActionParametersParser $parametersParser,
        MassActionDispatcher $massActionDispatcher,
        $massEditLimit
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
        $this->massEditLimit        = $massEditLimit;
    }

    /**
     * @Template
     * @AclAncestor("pim_enrich_mass_edit")
     * @return template|RedirectResponse
     */
    public function chooseAction()
    {
        if ($this->exceedMassEditLimit()) {
            return $this->redirectToRoute('pim_enrich_product_index');
        }

        $form = $this->getOperatorForm();

        if ($this->request->isMethod('POST')) {
            $form->submit($this->request);
            if ($form->isValid()) {
                return $this->redirectToRoute(
                    'pim_enrich_mass_edit_action_configure',
                    $this->getQueryParams() + ['operationAlias' => $this->operator->getOperationAlias()]
                );
            }
        }

        return array(
            'form'         => $form->createView(),
            'productCount' => $this->getProductCount(),
            'queryParams'  => $this->getQueryParams()
        );
    }

    /**
     * @param string  $operationAlias
     *
     * @AclAncestor("pim_enrich_mass_edit")
     * @throws NotFoundHttpException
     * @return template|RedirectResponse
     */
    public function configureAction($operationAlias)
    {
        try {
            $this->operator->setOperationAlias($operationAlias);
        } catch (\InvalidArgumentException $e) {
            throw $this->createNotFoundException($e->getMessage(), $e);
        }

        if ($this->exceedMassEditLimit()) {
            return $this->redirectToRoute('pim_enrich_product_index');
        }

        $this->operator->initializeOperation($this->getGridQB());
        $form = $this->getOperatorForm();

        if ($this->request->isMethod('POST')) {
            $form->submit($this->request);
            $this->operator->initializeOperation($this->getGridQB());
            $form = $this->getOperatorForm();
        }

        return $this->render(
            sprintf('PimEnrichBundle:MassEditAction:configure/%s.html.twig', $operationAlias),
            array(
                'form'         => $form->createView(),
                'operator'     => $this->operator,
                'productCount' => $this->getProductCount(),
                'queryParams'  => $this->getQueryParams()
            )
        );
    }

    /**
     * @param string  $operationAlias
     *
     * @AclAncestor("pim_enrich_mass_edit")
     * @throws NotFoundHttpException
     * @return template|RedirectResponse
     */
    public function performAction($operationAlias)
    {
        try {
            $this->operator->setOperationAlias($operationAlias);
        } catch (\InvalidArgumentException $e) {
            throw $this->createNotFoundException($e->getMessage(), $e);
        }

        if ($this->exceedMassEditLimit()) {
            return $this->redirectToRoute('pim_enrich_product_index');
        }

        $this->operator->initializeOperation($this->getGridQB());
        $form = $this->getOperatorForm();
        $form->submit($this->request);

        // Binding does not actually perform the operation, thus form errors can miss some constraints
        $this->operator->performOperation($this->getGridQB());
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
            $this->operator->finalizeOperation($this->getGridQB());
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
                'queryParams'  => $this->getQueryParams()
            )
        );
    }

    /**
     * Temporary method to avoid editing too many products
     *
     * @return boolean
     *
     * @deprecated
     */
    protected function exceedMassEditLimit()
    {
        $productCount = $this->getProductCount($this->request);
        if ($exceed = ($productCount > $this->massEditLimit)) {
            $this->addFlash('error', 'pim_enrich.mass_edit_action.limit_exceeded', ['%limit%' => $this->massEditLimit]);
        }

        return $exceed;
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
     * @return integer
     */
    protected function getProductCount()
    {
        $qb = clone $this->getGridQB();
        $rootEntity = current($qb->getRootEntities());
        $rootAlias  = $qb->getRootAlias();
        $rootField  = $rootAlias.'.id';
        $qb->resetDQLPart('select');
        $qb->select($rootField);
        $qb->add('from', new From($rootEntity, $rootAlias), false);
        $qb->groupBy($rootField);
        $ids = $qb->getQuery()->getResult(AbstractQuery::HYDRATE_ARRAY);

        return count($ids);
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
     * Get the query builder with grid parameters applied
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function getGridQB()
    {
        if (null === $this->gridQB) {
            $qb = $this->massActionDispatcher->dispatch($this->request);

            $this->gridQB = $qb;
        }

        return $this->gridQB;
    }
}
