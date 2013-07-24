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
     * Find an entity
     * @param string  $repository Example: 'PimProductBundle:Product'
     * @param integer $id
     *
     * @throws NotFoundHttpException
     * @return mixed
     */
    protected function findOr404($repository, $id)
    {
        $result = $this->getEntityManager()->getRepository($repository)->find($id);

        if (!$result) {
            throw $this->createNotFoundException(sprintf('%s entity not found', end(explode(':', $repository))));
        }

        return $result;
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
     * Get the log entries datagrid for the given product
     *
     * @param mixed  $entity
     * @param string $route
     * @param array  $routeParams
     *
     * @return Oro\Bundle\GridBundle\Datagrid\Datagrid
     */
    protected function getDataAuditDatagrid($entity, $route, array $routeParams)
    {
        if (!is_object($entity)) {
            throw new \InvalidArgumentException(
                sprintf('Expected Object argument, got %s', gettype($entity))
            );
        }
        $queryFactory = $this->get('pim_product.datagrid.manager.history.default_query_factory');

        // TODO Change query builder to $this->getDataAuditRepository()->getLogEntriesQueryBuilder($product)
        //      when BAP will be up-to-date. This is currently not achievable quickly because of the introduction
        //      of the OroAsseticBundle that breaks the PIM UI.
        $qb = $this
            ->getDataAuditRepository()
            ->createQueryBuilder('a')
            ->where('a.objectId = :objectId AND a.objectClass = :objectClass')
            ->orderBy('a.loggedAt', 'DESC')
            ->setParameters(
                array(
                    'objectId'    => $entity->getId(),
                    'objectClass' => get_class($entity)
                )
            );

        $queryFactory->setQueryBuilder($qb);

        $datagridManager = $this->get('pim_product.datagrid.manager.history');
        $datagridManager->getRouteGenerator()->setRouteName($route);
        $datagridManager->getRouteGenerator()->setRouteParameters($routeParams);

        return $datagridManager->getDatagrid();
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

    /**
     * Get the data audit doctrine repository
     *
     * @return AuditRepository
     */
    protected function getDataAuditRepository()
    {
        return $this
            ->getDoctrine()
            ->getRepository('OroDataAuditBundle:Audit');
    }
}
