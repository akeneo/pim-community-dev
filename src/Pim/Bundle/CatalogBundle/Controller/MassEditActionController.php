<?php

namespace Pim\Bundle\CatalogBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Validator\ValidatorInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Form\FormError;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\DataGridBundle\Extension\MassAction\MassActionParametersParser;
use Oro\Bundle\DataGridBundle\Action\MassAction\MassActionResponse;
use Oro\Bundle\DataGridBundle\Datagrid\Manager;

use Pim\Bundle\CatalogBundle\Form\Type\MassEditActionOperatorType;
use Pim\Bundle\CatalogBundle\AbstractController\AbstractDoctrineController;
use Pim\Bundle\CatalogBundle\MassEditAction\MassEditActionOperator;
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
    protected $massEditActionOperator;

    /** @var Manager */
    protected $manager;

    /** @var MassActionParametersParser */
    protected $parametersParser;

    /** @var ProductManager */
    protected $productManager;

    /** @var ValidatorInterface */
    protected $validator;

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
     * @param MassEditActionOperator     $massEditActionOperator
     * @param Manager                    $manager
     * @param MassActionParametersParser $parametersParser
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
        MassEditActionOperator $massEditActionOperator,
        Manager $manager,
        MassActionParametersParser $parametersParser,
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

        $this->validator              = $validator;
        $this->massEditActionOperator = $massEditActionOperator;
        $this->manager                = $manager;
        $this->parametersParser       = $parametersParser;
        $this->productManager         = $productManager;
    }

    /**
     * @param Request $request
     *
     * @Template
     * @AclAncestor("pim_catalog_mass_edit")
     * @return template|RedirectResponse
     */
    public function chooseAction(Request $request)
    {
        $productIds = $this->getProductIds($request);
        if (!$productIds) {
            return $this->redirectToRoute('pim_catalog_product_index');
        }

        $form = $this->getMassEditActionOperatorForm();

        if ($request->isMethod('POST')) {
            $form->submit($request);
            if ($form->isValid()) {
                return $this->redirectToRoute(
                    'pim_catalog_mass_edit_action_configure',
                    array(
                        'products'       => $productIds,
                        'operationAlias' => $this->massEditActionOperator->getOperationAlias(),
                    )
                );
            }
        }

        return array(
            'form'       => $form->createView(),
            'productIds' => $productIds,
        );
    }

    /**
     * @param Request $request
     * @param string  $operationAlias
     *
     * @AclAncestor("pim_catalog_mass_edit")
     * @throws NotFoundHttpException
     * @return template|RedirectResponse
     */
    public function configureAction(Request $request, $operationAlias)
    {
        try {
            $this->massEditActionOperator->setOperationAlias($operationAlias);
        } catch (\InvalidArgumentException $e) {
            throw $this->createNotFoundException($e->getMessage(), $e);
        }

        $productIds = $this->getProductIds($request);
        if (!$productIds) {
            return $this->redirectToRoute('pim_catalog_product_index');
        }

        $this->massEditActionOperator->initializeOperation($productIds);
        $form = $this->getMassEditActionOperatorForm();

        if ($request->isMethod('POST')) {
            $form->submit($request);
            $this->massEditActionOperator->initializeOperation($productIds);
            $form = $this->getMassEditActionOperatorForm();
        }

        return $this->render(
            sprintf('PimCatalogBundle:MassEditAction:configure/%s.html.twig', $operationAlias),
            array(
                'form'          => $form->createView(),
                'massEditActionOperator' => $this->massEditActionOperator,
                'productIds'    => $productIds,
            )
        );
    }

    /**
     * @param Request $request
     * @param string  $operationAlias
     *
     * @AclAncestor("pim_catalog_mass_edit")
     * @throws NotFoundHttpException
     * @return template|RedirectResponse
     */
    public function performAction(Request $request, $operationAlias)
    {
        try {
            $this->massEditActionOperator->setOperationAlias($operationAlias);
        } catch (\InvalidArgumentException $e) {
            throw $this->createNotFoundException($e->getMessage(), $e);
        }

        $productIds = $this->getProductIds($request);
        if (!$productIds) {
            return $this->redirectToRoute('pim_catalog_product_index');
        }

        // Hacky hack for the edit common attribute operation to work
        // first time is to set diplayed attributes and locale
        $this->massEditActionOperator->initializeOperation($productIds);
        $form = $this->getMassEditActionOperatorForm();
        $form->submit($request);

        //second time is to set values
        $this->massEditActionOperator->initializeOperation($productIds);
        $form = $this->getMassEditActionOperatorForm();
        $form->submit($request);

        // Binding does not actually perform the operation, thus form errors can miss some constraints
        $this->massEditActionOperator->performOperation($productIds);
        foreach ($this->validator->validate($this->massEditActionOperator) as $violation) {
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
                $this->productManager->findByIds($productIds),
                false
            );
            $this->addFlash(
                'success',
                sprintf('pim_catalog.mass_edit_action.%s.success_flash', $operationAlias)
            );

            return $this->redirectToRoute('pim_catalog_product_index');
        }

        return $this->render(
            sprintf('PimCatalogBundle:MassEditAction:configure/%s.html.twig', $operationAlias),
            array(
                'form'                   => $form->createView(),
                'massEditActionOperator' => $this->massEditActionOperator,
                'productIds'             => $productIds,
            )
        );
    }

    /**
     * @param Request $request
     *
     * @return Response
     * @throws \LogicException
     */
    public function deleteAction(Request $request)
    {
        $productIds = $this->getProductIds($request);
        $this->productManager->removeAll($productIds);
        $entitiesCount = count($productIds);
        $options = array('count' => $entitiesCount);

        $response = new MassActionResponse(
            true,
            $this->getTranslator()->transChoice(
                'oro.grid.mass_action.delete.success_message',
                $entitiesCount,
                array('%count%' => $entitiesCount)
            ),
            $options
        );

        $data = array(
            'successful' => $response->isSuccessful(),
            'message'    => $response->getMessage(),
        );

        return new JsonResponse(array_merge($data, $response->getOptions()));
    }

    /**
     * @return Form
     */
    protected function getMassEditActionOperatorForm()
    {
        return $this->createForm(
            new MassEditActionOperatorType(),
            $this->massEditActionOperator,
            array('operations' => $this->massEditActionOperator->getOperationChoices())
        );
    }

    /**
     * Get the product ids stored in the query string
     *
     * @param Request $request
     *
     * @return array
     */
    protected function getProductIds(Request $request)
    {
        $parameters = $this->parametersParser->parse($request);

        if ($parameters['inset'] === false) {
            $datagrid = $this->manager->getDatagrid('product-grid');
            $qb       = $datagrid->getAcceptedDatasource()->getQueryBuilder();

            $whereParts = $qb->getDQLPart('where')->getParts();
            $qb->resetDQLPart('where');

            // Remove 'entityIds' from the querybuilder to be able to get all results
            foreach ($whereParts as $part) {
                if (!is_string($part) || !strpos($part, 'entityIds')) {
                    $qb->andWhere($part);
                }
            }
            $qb->setParameters(
                $qb->getParameters()->filter(
                    function ($parameter) {
                        return $parameter->getName() !== 'entityIds';
                    }
                )
            );

            $results = $qb->getQuery()->execute();

            $ids = array_map(
                function ($result) {
                    return $result[0]->getId();
                },
                $results
            );

            return array_unique(array_diff($ids, $parameters['values']));

        } elseif (!empty($parameters['values'])) {
            return $parameters['values'];

        } else {
            return $request->query->get('products');
        }
    }
}
