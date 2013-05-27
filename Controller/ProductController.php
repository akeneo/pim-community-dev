<?php

namespace Pim\Bundle\ProductBundle\Controller;

use Pim\Bundle\ProductBundle\Manager\ProductManager;

use Pim\Bundle\ProductBundle\Entity\ProductPrice;

use Symfony\Component\HttpFoundation\Response;

use Pim\Bundle\ProductBundle\Entity\AttributeGroup;
use Pim\Bundle\ProductBundle\Manager\MediaManager;
use Pim\Bundle\ProductBundle\Entity\Product;
use Pim\Bundle\ProductBundle\Form\Type\ProductType;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use YsTools\BackUrlBundle\Annotation\BackUrl;
use Pim\Bundle\ProductBundle\Model\AvailableProductAttributes;

/**
 * Product Controller
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class ProductController extends Controller
{
    /**
     * List product attributes
     *
     * @param Request $request the request
     *
     * @Route("/index.{_format}",
     *      requirements={"_format"="html|json"},
     *      defaults={"_format" = "html"}
     * )
     * @return template
     */
    public function indexAction(Request $request)
    {
        $this->getProductManager()->setLocale($this->getDataLocale());

        /** @var $gridManager ProductDatagridManager */
        $gridManager = $this->get('pim_product.product_grid_manager');
        $datagrid = $gridManager->getDatagrid();

        if ('json' == $request->getRequestFormat()) {
            $view = 'OroGridBundle:Datagrid:list.json.php';
        } else {
            $view = 'PimProductBundle:Product:index.html.twig';
        }

        return $this->render($view, array('datagrid' => $datagrid->createView()));
    }

    /**
     * Create product
     *
     * @param string $dataLocale data locale
     *
     * @Route("/create/{dataLocale}", defaults={"dataLocale" = null})
     * @Template("PimProductBundle:Product:edit.html.twig")
     *
     * @return array
     */
    public function createAction($dataLocale)
    {
        $entity = $this->getProductManager()->createFlexible(true);

        return $this->editAction($entity, $dataLocale);
    }

    /**
     * Create product using simple form
     *
     * @param string $dataLocale data locale
     *
     * @Route("/quickcreate/{dataLocale}", defaults={"dataLocale" = null})
     * @Template("PimProductBundle:Product:quickcreate.html.twig")
     *
     * @return array
     */
    public function quickCreateAction($dataLocale)
    {
        $entity = $this->getProductManager()->createFlexible(true);

        if ($this->get('pim_product.form.handler.simple_product')->process($entity)) {
            $this->get('session')->getFlashBag()->add('success', 'Product successfully saved');

            $dataLocale = $entity->getActiveLanguages()->first()->getLanguage()->getCode();
            $url = $this->generateUrl(
                'pim_product_product_edit',
                array('id' => $entity->getId(), 'dataLocale' => $dataLocale)
            );
            $response = array('status' => 1, 'url' => $url);

            return new Response(json_encode($response));
        }

        $request = $this->getRequest();

        return array(
            'form'       => $this->get('pim_product.form.simple_product')->createView(),
            'dataLocale' => $this->getDataLocale()
        );
    }

    /**
     * Edit product
     *
     * @param integer $id the product id
     *
     * @Route(
     *     "{id}/edit",
     *     requirements={"id"="\d+"}
     * )
     * @Template
     *
     * @return array
     */
    public function editAction($id)
    {
        $product = $this->findProductOr404($id);
        $request = $this->getRequest();

        // create form
        $form     = $this->createForm('pim_product', $product);
        $channels = $this->getChannelRepository()->findAll();

        if ($request->getMethod() == 'POST') {
            $form->bind($request);

            if ($form->isValid()) {
                $this->getProductManager()->save($product);

                $this->addFlash('success', 'Product successfully saved');

                return $this->redirect(
                    $this->generateUrl(
                        'pim_product_product_edit',
                        array(
                            'id'         => $product->getId(),
                            'dataLocale' => $this->getDataLocale()
                        )
                    )
                );
            }
        }

        return array(
            'form'           => $form->createView(),
            'dataLocale'     => $this->getDataLocale(),
            'channels'       => $channels,
            'attributesForm' => $this->getAvailableProductAttributesForm($product->getAttributes())->createView(),
            'product'        => $product,
        );
    }

    /**
     * Add attributes to product
     *
     * @param int $id The product id to which add attributes
     *
     * @return Symfony\Component\HttpFoundation\RedirectResponse
     *
     * @Route("/{id}/attributes", requirements={"id"="\d+", "_method"="POST"})
     *
     */
    public function addProductAttributes($id)
    {
        $product             = $this->findProductOr404($id);
        $availableAttributes = new AvailableProductAttributes;
        $attributesForm      = $this->getAvailableProductAttributesForm(
            $product->getAttributes(), $availableAttributes
        );
        $attributesForm->bind($this->getRequest());

        foreach ($availableAttributes->getAttributes() as $attribute) {
            $value = $this->getProductManager()->createFlexibleValue();
            $value->setAttribute($attribute);
            $value->setData(null);
            $product->addValue($value);
        }

        $this->getProductManager()->save($product);

        return $this->redirect(
            $this->generateUrl('pim_product_product_edit', array('id' => $product->getId()))
        );
    }

    /**
     * Remove product
     *
     * @param Product $product The product to remove
     *
     * @Route("/remove/{id}", requirements={"id"="\d+"})
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function removeAction(Product $product)
    {
        $em = $this->getProductManager()->getStorageManager();
        $em->remove($product);
        $em->flush();

        if ($this->getRequest()->isXmlHttpRequest()) {
            return new Response('', 204);
        } else {
            return $this->redirect($this->generateUrl('pim_product_product_index'));
        }
    }

    /**
     * Remove an attribute value
     *
     * @Route("/{productId}/attributes/{attributeId}")
     * @Method("DELETE")
     */
    public function removeProductValueAction($productId, $attributeId)
    {
        $values = $this->getProductValueRepository()->findBy(array(
            'entity'    => $productId,
            'attribute' => $attributeId,
        ));

        if (false === $this->checkValuesRemovability($values)) {
            throw $this->createNotFoundException(sprintf(
                'Could not find removable product attribute for product %d with id %d',
                $productId, $attributeId
            ));
        }

        $em = $this->getEntityManager();
        foreach ($values as $value) {
            $em->remove($value);
        }
        $em->flush();

        $this->addFlash('success', 'Attribute was successfully removed.');

        return $this->redirect($this->generateUrl(
            'pim_product_product_edit', array('id' => $productId)
        ));
    }

    /**
     * Get product manager
     *
     * @return ProductManager
     */
    protected function getProductManager()
    {
        $pm = $this->container->get('product_manager');
        $pm->setLocale($this->getDataLocale());

        return $pm;
    }

    /**
     * Get data locale
     *
     * @throws \Exception
     *
     * @return string
     */
    protected function getDataLocale()
    {
        $dataLocale = $this->getRequest()->get('dataLocale');
        if ($dataLocale === null) {
            $dataLocale = (string) $this->getUser()->getValue('cataloglocale');
        }
        if (!$dataLocale) {
            throw new \Exception('User must have a catalog locale defined');
        }

        return $dataLocale;
    }

    /**
     * Get data scope
     *
     * @throws \Exception
     *
     * @return string
     */
    protected function getDataScope()
    {
        $dataScope = $this->getRequest()->get('dataScope');
        if ($dataScope === null) {
            $dataScope = (string) $this->getUser()->getValue('catalogScope');
        }
        if (!$dataScope) {
            throw new \Exception('User must have a catalog scope defined');
        }

        return $dataScope;
    }

    /**
     * Get the ProductAttribute entity repository
     *
     * @return Pim\Bundle\ProductBundle\Entity\Repository\ProductAttributeRepository
     */
    protected function getProductAttributeRepository()
    {
        return $this->getProductManager()->getAttributeRepository();
    }

    /**
     * Get the AttributeGroup entity repository
     *
     * @return Pim\Bundle\ProductBundle\Entity\Repository\AttributeGroupRepository
     */
    protected function getAttributeGroupRepository()
    {
        return $this
            ->getDoctrine()
            ->getRepository('PimProductBundle:AttributeGroup');
    }

    /**
     * Get the Channel entity repository
     *
     * @return Doctrine\ORM\EntityRepository
     */
    protected function getChannelRepository()
    {
        return $this->getDoctrine()->getRepository('PimConfigBundle:Channel');
    }

    /**
     * Get the product value entity repository
     *
     * @return Doctrine\ORM\EntityRepository
     */
    protected function getProductValueRepository()
    {
        return $this->getProductManager()->getFlexibleValueRepository();
    }

    /**
     * Get the container parameter value
     *
     * @param string $name the container parameter name
     *
     * @return string
     */
    protected function getParameter($name)
    {
        return $this->container->getParameter($name);
    }

    /**
     * Find a product by its id or return a 404 response
     *
     * @param int $id the product id
     *
     * @return Pim\Bundle\ProductBundle\Entity\Product
     *
     * @throw Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    private function findProductOr404($id)
    {
        $product = $this->getProductManager()->localizedFind($id);
        $currencyManager = $this->container->get('pim_config.manager.currency');

        if (!$product) {
            throw $this->createNotFoundException(
                sprintf('Product with id %d could not be found.', $id)
            );
        }
        $this->getProductManager()->addMissingPrices($currencyManager, $product);

        return $product;
    }

    private function checkValuesRemovability(array $values)
    {
        if (0 === count($values)) {
            return false;
        }

        foreach ($values as $value) {
            if (!$value->isRemovable()) {
                return false;
            }
        }

        return true;
    }
}
