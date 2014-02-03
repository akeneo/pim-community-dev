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

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\DataGridBundle\Extension\MassAction\MassActionParametersParser;
use Oro\Bundle\DataGridBundle\Extension\MassAction\MassActionDispatcher;

use Pim\Bundle\EnrichBundle\Form\Type\MassEditActionOperatorType;
use Pim\Bundle\EnrichBundle\AbstractController\AbstractDoctrineController;
use Pim\Bundle\EnrichBundle\MassEditAction\MassEditActionOperator;
use Pim\Bundle\CatalogBundle\Manager\ProductManager;

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

    /** @var ProductManager */
    protected $productManager;

    /** @var ValidatorInterface */
    protected $validator;

    /** @var integer[] */
    protected $productIds;

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
     * @param ProductManager             $productManager
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
        MassActionDispatcher $massActionDispatcher,
        ProductManager $productManager
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
        $this->productManager       = $productManager;
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

        $this->operator->initializeOperation($this->getProductIds($request));
        $form = $this->getOperatorForm();

        if ($request->isMethod('POST')) {
            $form->submit($request);
            $this->operator->initializeOperation($this->getProductIds($request));
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

        // Hacky hack for the edit common attribute operation to work
        // first time is to set diplayed attributes and locale
        $this->operator->initializeOperation($this->getProductIds($request));
        $form = $this->getOperatorForm();
        $form->submit($request);

        //second time is to set values
        $this->operator->initializeOperation($this->getProductIds($request));
        $form = $this->getOperatorForm();
        $form->submit($request);

        // Binding does not actually perform the operation, thus form errors can miss some constraints
        $this->operator->performOperation($this->getProductIds($request));
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
            $this->productManager->saveAll(
                $this->productManager->findByIds($this->getProductIds($request)),
                false
            );
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
     * Get the product ids to perform the mass action on
     *
     * @param Request $request
     *
     * @return array
     */
    protected function getProductIds(Request $request)
    {
        if (null === $this->productIds) {
            $qb = $this->getGridQB($request);

            $results = $qb->getQuery()->getResult();

            $this->productIds = array_map(
                function ($result) {
                    return $result[0]->getId();
                },
                $results
            );
        }

        return $this->productIds;
    }

    /**
     * Get the count of products to perform the mass action on
     *
     * @param Request $request
     *
     * @return array
     */
    protected function getProductCount(Request $request)
    {
        return count($this->getProductIds($request));
    }

    /**
     * Get the datagrid query parameters
     *
     * @param Request $request
     *
     * @return QueryBuilder
     */
    protected function getQueryParams(Request $request)
    {
        return $this->parametersParser->parse($request) + ['gridName' => $request->get('gridName')];
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
        $parameters  = $this->getQueryParams($request);
        $requestData = array_merge($request->query->all(), $request->request->all());

        return $this->massActionDispatcher->dispatch(
            $requestData['gridName'],
            'export',
            $parameters,
            $requestData
        );
    }
}
