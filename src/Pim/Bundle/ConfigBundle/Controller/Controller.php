<?php
namespace Pim\Bundle\ConfigBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller as BaseController;

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
            throw $this->createNotFoundException(sprintf('%s entity not found', end(explode(':', $repository))));
        }

        return $result;
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
