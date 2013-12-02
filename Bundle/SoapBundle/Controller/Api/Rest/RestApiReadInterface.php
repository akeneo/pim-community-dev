<?php

namespace Oro\Bundle\SoapBundle\Controller\Api\Rest;

use Symfony\Component\HttpFoundation\Response;

interface RestApiReadInterface
{
    /**
     * Get paginated items list.
     *
     * @param  int      $page
     * @param  int      $limit
     * @return Response
     */
    public function handleGetListRequest($page, $limit);

    /**
     * Get item by identifier.
     *
     * @param  mixed $id
     * @return mixed
     */
    public function handleGetRequest($id);
}
