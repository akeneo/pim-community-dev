<?php

namespace PimEnterprise\Bundle\EnrichBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use PimEnterprise\Bundle\UserBundle\Context\UserContext;
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
     * @var UserContext
     */
    protected $userContext;

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

    /**
     * Show product
     *
     * @param Request $request
     * @param integer $id
     *
     * @Template
     * @AclAncestor("pim_enrich_product_edit")
     * @return array
     */
    public function showAction(Request $request, $id)
    {
        $product = $this->findProductOr404($id);

        return [
            'product' => $product,
        ];
    }

    /**
     * Show a product value
     *
     * @param Request $requset
     * @param string  $productId
     * @param string  $attributeCode
     *
     * @return Response
     */
    public function showAttributeAction(Request $request, $productId, $attributeCode)
    {
        if (!$request->isXmlHttpRequest()) {
            throw $this->createNotFoundException();
        }

        $product = $this->findProductOr404($productId);
        $locale = $request->query->get('locale');
        $scope = $request->query->get('scope');

        $value = $product->getValue($attributeCode, $locale, $scope);

        return new Response((string) $value);
    }
}
