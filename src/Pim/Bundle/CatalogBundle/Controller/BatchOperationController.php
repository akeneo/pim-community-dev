<?php

namespace Pim\Bundle\CatalogBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Validator\ValidatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Pim\Bundle\CatalogBundle\Form\Type\BatchOperatorType;
use Pim\Bundle\CatalogBundle\AbstractController\AbstractController;
use Pim\Bundle\CatalogBundle\BatchOperation\BatchOperator;

/**
 * Batch operation controller
 *
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class BatchOperationController extends AbstractController
{
    protected $batchOperator;

    public function __construct(
        Request $request,
        EngineInterface $templating,
        RouterInterface $router,
        SecurityContextInterface $securityContext,
        FormFactoryInterface $formFactory,
        ValidatorInterface $validator,
        BatchOperator $batchOperator
    ) {
        parent::__construct($request, $templating, $router, $securityContext, $formFactory, $validator);

        $this->batchOperator = $batchOperator;
    }

    /**
     * @Template
     */
    public function chooseAction(Request $request)
    {
        if ($request->isMethod('GET')) {
            $productIds = $request->query->get('products');
            if (!$productIds || !is_array($productIds)) {
                return $this->redirectToRoute('pim_catalog_product_index');
            }
            $this->batchOperator->setProductIds($productIds);
        }

        $form = $this->getBatchOperatorForm();

        if ($request->isMethod('POST')) {
            $form->bind($request);
            if ($form->isValid()) {
                return $this->redirectToRoute(
                    'pim_catalog_batch_operation_configure',
                    array(
                        'products'       => $this->batchOperator->getProductIds(),
                        'operationAlias' => $this->batchOperator->getOperationAlias(),
                    )
                );
            }
        }

        return array(
            'form' => $form->createView(),
        );
    }

    /**
     * @Template
     */
    public function configureAction(Request $request, $operationAlias)
    {
        $this->batchOperator->setOperationAlias($operationAlias);

        if ($request->isMethod('GET')) {
            $productIds = $request->query->get('products');
            if (!$productIds || !is_array($productIds)) {
                return $this->redirectToRoute('pim_catalog_product_index');
            }
            $this->batchOperator->setProductIds($productIds);
        }

        $form = $this->getBatchOperatorForm();
        if ($request->isMethod('POST')) {
            $form->bind($request);
            if ($form->isValid()) {
                $this->batchOperator->performOperation();
            }
        }

        return array(
            'form'          => $form->createView(),
            'batchOperator' => $this->batchOperator,
        );
    }

    private function getBatchOperatorForm()
    {
        return $this->createForm(
            new BatchOperatorType(),
            $this->batchOperator,
            array('operations' => $this->batchOperator->getOperationChoices())
        );
    }
}
