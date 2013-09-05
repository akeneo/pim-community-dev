<?php

namespace Pim\Bundle\CatalogBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Validator\ValidatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Pim\Bundle\CatalogBundle\BatchOperation\BatchOperator;
use Pim\Bundle\CatalogBundle\Form\Type\BatchOperatorType;
use Pim\Bundle\CatalogBundle\AbstractController\AbstractController;

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
        $batchOperator = $this->getBatchOperator();

        if ($request->isMethod('GET')) {
            $productIds = $request->query->get('products');
            if (!$productIds || !is_array($productIds)) {
                return $this->redirectToRoute('pim_catalog_product_index');
            }
            $batchOperator->setProductIds($productIds);
        }

        $form = $this->getBatchOperatorForm($batchOperator);

        if ($request->isMethod('POST')) {
            $form->bind($request);
            if ($form->isValid()) {
                return $this->redirectToRoute(
                    'pim_catalog_batch_operation_configure',
                    array(
                        'products'       => $batchOperator->getProductIds(),
                        'operationAlias' => $batchOperator->getOperationAlias(),
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
        $batchOperator = $this->getBatchOperator();
        $batchOperator->setOperationAlias($operationAlias);

        if ($request->isMethod('GET')) {
            $productIds = $request->query->get('products');
            if (!$productIds || !is_array($productIds)) {
                return $this->redirectToRoute('pim_catalog_product_index');
            }
            $batchOperator->setProductIds($productIds);
        }

        $form = $this->getBatchOperatorForm($batchOperator);
        if ($request->isMethod('POST')) {
            $form->bind($request);
            if ($form->isValid()) {
                $batchOperator->performOperation();
            }
        }

        return array(
            'form'          => $form->createView(),
            'batchOperator' => $batchOperator,
        );
    }

    private function getBatchOperator()
    {
        return $this->batchOperator;
    }

    private function getBatchOperatorForm(BatchOperator $batchOperator)
    {
        return $this->createForm(
            new BatchOperatorType(),
            $batchOperator,
            array('operations' => $batchOperator->getOperationChoices())
        );
    }
}
