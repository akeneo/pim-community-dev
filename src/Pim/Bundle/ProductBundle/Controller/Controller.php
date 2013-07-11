<?php
namespace Pim\Bundle\ProductBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller as BaseController;
use Pim\Bundle\ProductBundle\Model\AvailableProductAttributes;
use Pim\Bundle\ProductBundle\Form\Type\AvailableProductAttributesType;

/**
 * Base controller
 *
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Controller extends BaseController
{
    /**
     * Custom method to generate url with a hash
     * @param string  $route      The name of the route
     * @param mixed   $parameters An array of parameters
     * @param Boolean $absolute   Whether to generate an absolute URL
     * @param string  $hash       The hash to prepend to the URL
     *
     * @return string
     */
    public function generateUrl($route, $parameters = array(), $absolute = false, $hash = null)
    {
        $url = parent::generateUrl($route, $parameters, $absolute);
        if (!$hash) {
            $hash = $this->getRequest()->query->get('hash');
        }
        if ($hash) {
            $url .= '#'.$hash;
        }

        return $url;
    }

    /**
     * @return ObjectManager
     */
    protected function getEntityManager()
    {
        return $this->getDoctrine()->getManager();
    }

    /**
     * Get the AvailbleProductAttributes form
     *
     * @param array                      $attributes          The product attributes
     * @param AvailableProductAttributes $availableAttributes The available attributes container
     *
     * @return Symfony\Component\Form\Form
     */
    protected function getAvailableProductAttributesForm(
        array $attributes = array(),
        AvailableProductAttributes $availableAttributes = null
    ) {
        return $this->createForm(
            new AvailableProductAttributesType,
            $availableAttributes ?: new AvailableProductAttributes,
            array('attributes' => $attributes)
        );
    }

    /**
     * Add flash message
     *
     * @param string $type    the flash type
     * @param string $message the flash message
     *
     * @return null
     */
    protected function addFlash($type, $message)
    {
        $this->get('session')->getFlashBag()->add($type, $message);
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
     * @return ProductManager
     */
    protected function getProductManager()
    {
        return $this->get('pim_product.manager.product');
    }
}
