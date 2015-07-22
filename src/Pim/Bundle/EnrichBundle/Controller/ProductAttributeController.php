<?php

namespace Pim\Bundle\EnrichBundle\Controller;

use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Pim\Bundle\CatalogBundle\Manager\ProductManager;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\AvailableAttributes;
use Pim\Bundle\CatalogBundle\Repository\AttributeRepositoryInterface;
use Pim\Bundle\EnrichBundle\Exception\DeleteException;
use Pim\Bundle\EnrichBundle\Flash\Message;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * Product attribute controller, allows to add and remove optional attributes to a product
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductAttributeController
{
    /** @var RouterInterface */
    protected $router;

    /** @var FormFactoryInterface */
    protected $formFactory;

    /** @var ProductManager */
    protected $productManager;

    /** @var AttributeRepositoryInterface */
    protected $attributeRepository;

    /**
     * Constructor
     *
     * @param RouterInterface              $router
     * @param FormFactoryInterface         $formFactory
     * @param ProductManager               $productManager
     * @param AttributeRepositoryInterface $attributeRepository
     */
    public function __construct(
        RouterInterface $router,
        FormFactoryInterface $formFactory,
        ProductManager $productManager,
        AttributeRepositoryInterface $attributeRepository
    ) {
        $this->router          = $router;
        $this->formFactory     = $formFactory;
        $this->productManager  = $productManager;
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * Add attributes to product
     *
     * @param Request $request The request object
     * @param integer $id      The product id to which add attributes
     *
     * @AclAncestor("pim_enrich_product_add_attribute")
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function addAttributesAction(Request $request, $id)
    {
        $product             = $this->findProductOr404($id);
        $availableAttributes = new AvailableAttributes();
        $attributesForm      = $this->getAvailableAttributesForm(
            $product->getAttributes(),
            $availableAttributes
        );
        $attributesForm->submit($request);

        $this->productManager->addAttributesToProduct($product, $availableAttributes);
        $this->addFlash($request, 'success', 'flash.product.attributes added');

        return $this->redirectToRoute('pim_enrich_product_edit', array('id' => $product->getId()));
    }

    /**
     * Remove an attribute form a product
     *
     * @param Request $request     The request object
     * @param integer $productId   The product id
     * @param integer $attributeId The attribute id
     *
     * @AclAncestor("pim_enrich_product_remove_attribute")
     * @throws NotFoundHttpException
     *
     * @return RedirectResponse
     */
    public function removeAttributeAction(Request $request, $productId, $attributeId)
    {
        $product   = $this->findProductOr404($productId);
        $attribute = $this->findAttributeOr404($attributeId);

        if ($product->isAttributeRemovable($attribute)) {
            $this->productManager->removeAttributeFromProduct($product, $attribute);
        } else {
            throw new DeleteException($this->getTranslator()->trans('product.attribute not removable'));
        }
        if ($request->isXmlHttpRequest()) {
            return new Response('', 204);
        } else {
            return $this->redirectToRoute('pim_enrich_product_edit', array('id' => $productId));
        }
    }

    /**
     * Find a product by its id or return a 404 response
     *
     * @param integer $id the product id
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     *
     * @return \Pim\Bundle\CatalogBundle\Model\ProductInterface
     */
    protected function findProductOr404($id)
    {
        $product = $this->productManager->find($id);

        if (!$product) {
            throw new NotFoundHttpException(
                sprintf('Product with id %s could not be found.', (string) $id)
            );
        }

        return $product;
    }

    /**
     * Find an attribute by its id or return a 404 response
     *
     * @param integer $id the attribute id
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     *
     * @return AttributeInterface
     */
    protected function findAttributeOr404($id)
    {
        $attribute = $this->attributeRepository->find($id);

        if (!$attribute) {
            throw new NotFoundHttpException(
                sprintf('Attribute with id %s could not be found.', (string) $id)
            );
        }

        return $attribute;
    }

    /**
     * Get the AvailableAttributes form
     *
     * @param array               $attributes          The attributes
     * @param AvailableAttributes $availableAttributes The available attributes container
     *
     * @return \Symfony\Component\Form\Form
     */
    protected function getAvailableAttributesForm(
        array $attributes = array(),
        AvailableAttributes $availableAttributes = null
    ) {
        return $this->formFactory->create(
            'pim_available_attributes',
            $availableAttributes ?: new AvailableAttributes(),
            array('excluded_attributes' => $attributes)
        );
    }

    /**
     * Create a redirection to a given route
     *
     * @param string  $route
     * @param mixed   $parameters
     * @param integer $status
     *
     * @return RedirectResponse
     */
    protected function redirectToRoute($route, $parameters = array(), $status = 302)
    {
        return $this->redirect($this->generateUrl($route, $parameters), $status);
    }

    /**
     * Returns a RedirectResponse to the given URL.
     *
     * @param string  $url    The URL to redirect to
     * @param integer $status The status code to use for the Response
     *
     * @return RedirectResponse
     */
    protected function redirect($url, $status = 302)
    {
        return new RedirectResponse($url, $status);
    }

    /**
     * Generates a URL from the given parameters.
     *
     * @param string         $route         The name of the route
     * @param mixed          $parameters    An array of parameters
     * @param boolean|string $referenceType The type of reference (one of the constants in UrlGeneratorInterface)
     *
     * @return string The generated URL
     *
     * @see UrlGeneratorInterface
     */
    protected function generateUrl($route, $parameters = array(), $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH)
    {
        return $this->router->generate($route, $parameters, $referenceType);
    }

    /**
     * Add flash message
     *
     * @param Request $request    the request
     * @param string  $type       the flash type
     * @param string  $message    the flash message
     * @param array   $parameters the flash message parameters
     *
     * @return null
     */
    protected function addFlash(Request $request, $type, $message, array $parameters = array())
    {
        $request->getSession()->getFlashBag()->add($type, new Message($message, $parameters));
    }
}
