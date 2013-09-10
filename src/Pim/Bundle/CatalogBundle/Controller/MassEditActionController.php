<?php

namespace Pim\Bundle\CatalogBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Validator\ValidatorInterface;
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
 */
class MassEditActionController extends AbstractController
{
    protected $batchOperator;

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
     */
    public function chooseAction(Request $request)
    {
        if ($values = $request->query->get('values')) {
            $productIds = explode(',', $values);
        } else {
            $productIds = $request->query->get('products');
        }

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

    public function configureAction(Request $request, $operationAlias)
    {
        try {
            $this->batchOperator->setOperationAlias($operationAlias);
        } catch (\InvalidArgumentException $e) {
            throw $this->createNotFoundException($e->getMessage(), $e);
        }

        $parameters = $request->query->all();
        try {
            $this->batchOperator->initializeOperation($parameters);
        } catch (\InvalidArgumentException $e) {
            return $this->redirectToRoute('pim_catalog_product_index');
        }
        $form = $this->getMassEditActionOperatorForm();

        if ($request->isMethod('POST')) {
            $form->bind($request);
            try {
                $this->batchOperator->initializeOperation($parameters);
            } catch (\InvalidArgumentException $e) {
                return $this->redirectToRoute('pim_catalog_product_index');
            }
            $form = $this->getMassEditActionOperatorForm();
        }

        return $this->render(
            sprintf('PimCatalogBundle:MassEditAction:configure/%s.html.twig', $operationAlias),
            array(
                'form'          => $form->createView(),
                'batchOperator' => $this->batchOperator,
                'parameters'    => $parameters,
            )
        );
    }

    public function performAction(Request $request, $operationAlias)
    {
        $this->batchOperator->setOperationAlias($operationAlias);

        $parameters = $request->query->all();

        // Hacky hack for the edit common attribute operation to work
        // first time is to set diplayed attributes and locale
        try {
            $this->batchOperator->initializeOperation($parameters);
        } catch (\InvalidArgumentException $e) {
            return $this->redirectToRoute('pim_catalog_product_index');
        }
        $form = $this->getMassEditActionOperatorForm();
        $form->bind($request);

        //second time is to set values
        try {
            $this->batchOperator->initializeOperation($parameters);
        } catch (\InvalidArgumentException $e) {
            return $this->redirectToRoute('pim_catalog_product_index');
        }
        $form = $this->getMassEditActionOperatorForm();
        $form->bind($request);

        $this->batchOperator->performOperation($parameters);

        $this->addFlash('success', sprintf('pim_catalog.mass_edit_action.%s.success_flash', $operationAlias));

        return $this->redirectToRoute('pim_catalog_product_index');
    }

    private function getMassEditActionOperatorForm()
    {
        return $this->createForm(
            new MassEditActionOperatorType(),
            $this->batchOperator,
            array('operations' => $this->batchOperator->getOperationChoices())
        );
    }
}
