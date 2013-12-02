<?php

namespace Oro\Bundle\SoapBundle\Controller\Api\Soap;

interface SoapApiReadInterface
{
    /**
     * Get item by identifier.
     *
     * @param  mixed  $id
     * @return object
     */
    public function handleGetRequest($id);

    /**
     * Get paginated items list.
     *
     * @param  int          $page
     * @param  int          $limit
     * @return \Traversable
     */
    public function handleGetListRequest($page, $limit);
}
