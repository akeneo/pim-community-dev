<?php

namespace Oro\Bundle\SoapBundle\Controller\Api\Soap;

use Oro\Bundle\SoapBundle\Controller\Api\EntityManagerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAware;

abstract class SoapGetController extends ContainerAware implements EntityManagerAwareInterface, SoapApiReadInterface
{
    /**
     * {@inheritDoc}
     */
    public function handleGetListRequest($page = 1, $limit = 10)
    {
        return $this->getManager()->getList($limit, $page);
    }

    /**
     * {@inheritDoc}
     */
    public function handleGetRequest($id)
    {
        return $this->getEntity($id);
    }

    /**
     * Get entity by identifier.
     *
     * @param  mixed      $id
     * @return object
     * @throws \SoapFault
     */
    protected function getEntity($id)
    {
        $entity = $this->getManager()->find($id);

        if (!$entity) {
            throw new \SoapFault('NOT_FOUND', sprintf('Record #%u can not be found', $id));
        }

        return $entity;
    }
}
