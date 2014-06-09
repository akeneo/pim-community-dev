<?php

namespace PimEnterprise\Bundle\EnrichBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Pim\Bundle\EnrichBundle\Controller\ProductController as BaseProductController;

/**
 * Product Controller
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class ProductController extends BaseProductController
{
    /**
     * {@inheritdoc}
     *
     * @AclAncestor("pim_enrich_product_index")
     * @Template
     * @return Response|RedirectResponse
     */
    public function indexAction(Request $request)
    {
        try {
            $this->userContext->getAccessibleUserTree();

            return parent::indexAction($request);
        } catch (\LogicException $e) {
            $this->addFlash('error', 'category.permissions.no_access_to_products');

            return $this->redirectToRoute('oro_default');
        }
    }
}
