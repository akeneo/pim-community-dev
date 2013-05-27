<?php

namespace Oro\Bundle\SoapBundle\Controller\Api;

use Oro\Bundle\SoapBundle\Entity\Manager\ApiEntityManager;

interface EntityManagerAwareInterface
{
    /**
     * Get entity Manager
     *
     * @return ApiEntityManager
     */
    public function getManager();
}
