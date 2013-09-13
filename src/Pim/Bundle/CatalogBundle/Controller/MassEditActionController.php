<?php

namespace Pim\Bundle\CatalogBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Validator\ValidatorInterface;
use Oro\Bundle\UserBundle\Annotation\Acl;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Pim\Bundle\CatalogBundle\Form\Type\MassEditActionOperatorType;
use Pim\Bundle\CatalogBundle\AbstractController\AbstractController;
use Pim\Bundle\CatalogBundle\MassEditAction\MassEditActionOperator;

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
class MassEditActionController extends AbstractController
{
    /**
     * @var MassEditActionOperator
     */
    protected $batchOperator;

    /**
     * Constructor
     *
     * @param Request                  $request
     * @param EngineInterface          $templating
     * @param RouterInterface          $router
     * @param SecurityContextInterface $securityContext
     * @param FormFactoryInterface     $formFactory
     * @param ValidatorInterface       $validator
     * @param MassEditActionOperator   $batchOperator
     */
    public function __construct(
        Request $request,
        EngineInterface $templating,
        RouterInterface $router,
        SecurityContextInterface $securityContext,
        FormFactoryInterface $formFactory,
        ValidatorInterface $validator,
        MassEditActionOperator $batchOperator
    ) {
        parent::__construct($request, $templating, $router, $securityContext, $formFactory, $validator);

        $this->batchOperator = $batchOperator;
    }

    /**
     * @Template
     * @Acl(
     *      id="pim_catalog_mass_edit_choose",
     *      name="Choose action",
     *      description="Choose action",
     *      parent="pim_catalog_mass_edit"
     * )
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
     * @Acl(
     *      id="pim_catalog_mass_edit_configure",
     *      name="Configure action",
     *      description="Configure action",
     *      parent="pim_catalog_mass_edit"
     * )
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
     *
     * @param Request $request
     * @param alis    $operationAlias
     * @Acl(
     *      id="pim_catalog_mass_edit_perform",
     *      name="Perform action",
     *      description="Perform action",
     *      parent="pim_catalog_mass_edit"
     * )
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
        if ($values = $request->query->get('values')) {
            return explode(',', $values);
        } else {
            return $request->query->get('products');
        }
    }
}
