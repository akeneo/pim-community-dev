<?php

namespace Pim\Bundle\CatalogBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Validator\ValidatorInterface;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Oro\Bundle\UserBundle\Annotation\Acl;
use Oro\Bundle\GridBundle\Action\MassAction\MassActionParametersParser;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Pim\Bundle\CatalogBundle\Form\Type\MassEditActionOperatorType;
use Pim\Bundle\CatalogBundle\AbstractController\AbstractDoctrineController;
use Pim\Bundle\CatalogBundle\MassEditAction\MassEditActionOperator;
use Symfony\Component\Translation\TranslatorInterface;
use Pim\Bundle\CatalogBundle\Datagrid\DatagridWorkerInterface;
use Oro\Bundle\GridBundle\Datagrid\ParametersInterface;

/**
 * Batch operation controller
 *
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @Acl(
 *      id="pim_catalog_mass_edit",
 *      name="Product mass edit actions",
 *      description="Product mass edit actions",
 *      parent="pim_catalog"
 * )
 */
class MassEditActionController extends AbstractDoctrineController
{
    /**
     * @var MassEditActionOperator
     */
    protected $batchOperator;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @var DatagridWorkerInterface
     */
    private $datagridWorker;

    /**
     * @var MassActionParametersParser
     */
    private $parametersParser;

    /**
     * Constructor
     *
     * @param Request                    $request
     * @param EngineInterface            $templating
     * @param RouterInterface            $router
     * @param SecurityContextInterface   $securityContext
     * @param FormFactoryInterface       $formFactory
     * @param ValidatorInterface         $validator
     * @param RegistryInterface          $doctrine
     * @param MassEditActionOperator     $batchOperator
     * @param TranslatorInterface        $translator
     * @param DatagridWorkerInterface    $datagridWorker
     * @param MassActionParametersParser $parametersParser
     */
    public function __construct(
        Request $request,
        EngineInterface $templating,
        RouterInterface $router,
        SecurityContextInterface $securityContext,
        FormFactoryInterface $formFactory,
        ValidatorInterface $validator,
        RegistryInterface $doctrine,
        MassEditActionOperator $batchOperator,
        TranslatorInterface $translator,
        DatagridWorkerInterface $datagridWorker,
        MassActionParametersParser $parametersParser
    ) {
        parent::__construct($request, $templating, $router, $securityContext, $formFactory, $validator, $doctrine);

        $this->batchOperator    = $batchOperator;
        $this->translator       = $translator;
        $this->datagridWorker   = $datagridWorker;
        $this->parametersParser = $parametersParser;
    }

    /**
     * @param Request $request
     *
     * @Template
     * @Acl(
     *      id="pim_catalog_mass_edit_choose",
     *      name="Choose action",
     *      description="Choose action",
     *      parent="pim_catalog_mass_edit"
     * )
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
            $form->bind($request);
            if ($form->isValid()) {
                return $this->redirectToRoute(
                    'pim_catalog_mass_edit_action_configure',
                    array(
                        'products'       => $productIds,
                        'operationAlias' => $this->batchOperator->getOperationAlias(),
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
     * @Acl(
     *      id="pim_catalog_mass_edit_configure",
     *      name="Configure action",
     *      description="Configure action",
     *      parent="pim_catalog_mass_edit"
     * )
     * @throws NotFoundHttpException
     * @return template|RedirectResponse
     */
    public function configureAction(Request $request, $operationAlias)
    {
        try {
            $this->batchOperator->setOperationAlias($operationAlias);
        } catch (\InvalidArgumentException $e) {
            throw $this->createNotFoundException($e->getMessage(), $e);
        }

        $productIds = $this->getProductIds($request);
        if (!$productIds) {
            return $this->redirectToRoute('pim_catalog_product_index');
        }

        $this->batchOperator->initializeOperation($productIds);
        $form = $this->getMassEditActionOperatorForm();

        if ($request->isMethod('POST')) {
            $form->bind($request);
            $this->batchOperator->initializeOperation($productIds);
            $form = $this->getMassEditActionOperatorForm();
        }

        return $this->render(
            sprintf('PimCatalogBundle:MassEditAction:configure/%s.html.twig', $operationAlias),
            array(
                'form'          => $form->createView(),
                'batchOperator' => $this->batchOperator,
                'productIds'    => $productIds,
            )
        );
    }

    /**
     * @param Request $request
     * @param string  $operationAlias
     *
     * @Acl(
     *      id="pim_catalog_mass_edit_perform",
     *      name="Perform action",
     *      description="Perform action",
     *      parent="pim_catalog_mass_edit"
     * )
     * @throws NotFoundHttpException
     * @return template|RedirectResponse
     */
    public function performAction(Request $request, $operationAlias)
    {
        try {
            $this->batchOperator->setOperationAlias($operationAlias);
        } catch (\InvalidArgumentException $e) {
            throw $this->createNotFoundException($e->getMessage(), $e);
        }

        $productIds = $this->getProductIds($request);
        if (!$productIds) {
            return $this->redirectToRoute('pim_catalog_product_index');
        }

        // Hacky hack for the edit common attribute operation to work
        // first time is to set diplayed attributes and locale
        $this->batchOperator->initializeOperation($productIds);
        $form = $this->getMassEditActionOperatorForm();
        $form->bind($request);

        //second time is to set values
        $this->batchOperator->initializeOperation($productIds);
        $form = $this->getMassEditActionOperatorForm();
        $form->bind($request);

        if ($form->isValid()) {
            $this->batchOperator->performOperation($productIds);
            $this->addFlash('success', sprintf('pim_catalog.mass_edit_action.%s.success_flash', $operationAlias));

            return $this->redirectToRoute('pim_catalog_product_index');
        }

        return $this->render(
            sprintf('PimCatalogBundle:MassEditAction:configure/%s.html.twig', $operationAlias),
            array(
                'form'          => $form->createView(),
                'batchOperator' => $this->batchOperator,
                'productIds'    => $productIds,
            )
        );
    }

    /**
     * @return Form
     */
    private function getMassEditActionOperatorForm()
    {
        return $this->createForm(
            new MassEditActionOperatorType(),
            $this->batchOperator,
            array('operations' => $this->batchOperator->getOperationChoices())
        );
    }

    /**
     * Get the product ids stored in the query string
     *
     * @param Request $request
     *
     * @return array
     */
    private function getProductIds(Request $request)
    {
        $inset = $request->query->get('inset');
        if ($inset === '0') {
            /** @var $gridManager ProductDatagridManager */
            $gridManager = $this->datagridWorker->getDatagridManager('product');
            $gridManager->setFilterTreeId($request->get('treeId', 0));
            $gridManager->setFilterCategoryId($request->get('categoryId', 0));
            $parameters = $this->parametersParser->parse($request);
            $filters = $parameters['filters'];
            $datagrid = $gridManager->getDatagrid();
            $datagrid->getParameters()->set(ParametersInterface::FILTER_PARAMETERS, $filters);
            $ids = $datagrid->getAllIds();

            return $ids;

        } elseif ($values = $request->query->get('values')) {
            return explode(',', $values);

        } else {
            return $request->query->get('products');
        }
    }

    /**
     * Manual flash translator
     * Otherwise, flash messages are not translated...
     *
     * @param string $type
     * @param string $message
     *
     * TODO Fix flash translation
     */
    protected function addFlash($type, $message)
    {
        parent::addFlash(
            $type,
            $this->translator->trans($message)
        );
    }
}
