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
     * @return ObjectManager
     */
    protected function getManager()
    {
        return $this->getDoctrine()->getManager();
    }

    /**
     * @return ObjectManager
     */
    protected function getEntityManager()
    {
        return $this->getManager();
    }

    /**
     * @param string $repository
     *
     * @return Repository
     */
    protected function getRepository($repository)
    {
        return $this->getManager()->getRepository($repository);
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
        $result = $this->getRepository($repository)->find($id);

        if (!$result) {
            throw $this->createNotFoundException(sprintf('%s entity not found', $repository));
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
        $queryFactory->setQueryBuilder(
            $this->getRepository('OroDataAuditBundle:Audit')->getLogEntriesQueryBuilder($entity)
        );

        $datagridManager = $this->get('pim_product.datagrid.manager.history');
        $datagridManager->getRouteGenerator()->setRouteName($route);
        $datagridManager->getRouteGenerator()->setRouteParameters($routeParams);

        return $datagridManager->getDatagrid();
    }

    /**
     * @return ProductManager
     */
    protected function getProductManager()
    {
        return $this->get('pim_product.manager.product');
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
     * Get the validator
     *
     * @return Symfony\Component\Validator\ValidatorInterface
     */
    protected function getValidator()
    {
        return $this->get('validator');
    }

    /**
     * Persist an entity
     *
     * @param object  $entity
     * @param boolean $flush
     */
    protected function persist($entity, $flush = true)
    {
        $this->getManager()->persist($entity);

        if ($flush) {
            $this->flush();
        }
    }

    /**
     * Remove an entity
     *
     * @param object  $entity
     * @param boolean $flush
     */
    protected function remove($entity, $flush = true)
    {
        $this->getManager()->remove($entity);

        if ($flush) {
            $this->flush();
        }
    }

    /**
     * Flush
     *
     * @param object|null $entity
     */
    protected function flush($entity = null)
    {
        $this->getManager()->flush($entity);
    }
}
