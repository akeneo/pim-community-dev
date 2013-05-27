<?php

namespace Oro\Bundle\SoapBundle\Controller\Api\Rest;

use Symfony\Component\HttpFoundation\Response;

interface RestApiCrudInterface
{
    /**
     * Create item.
     *
     * @return Response
     */
    public function handleCreateRequest();

    /**
     * Get paginated items list.
     *
     * @param int $page
     * @param int $limit
     * @return Response
     */
    public function handleGetListRequest($page, $limit);

    /**
     * Get item by identifier.
     *
     * @param mixed $id
     * @return mixed
     */
    public function handleGetRequest($id);

    /**
     * Update item.
     *
     * @param mixed $id
     * @return Response
     */
    public function handleUpdateRequest($id);

    /**
     * Delete item.
     *
     * @param mixed $id
     * @return Response
     */
    public function handleDeleteRequest($id);
}
