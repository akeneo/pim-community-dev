<?php

namespace Oro\Bundle\SoapBundle\Controller\Api\Soap;

interface SoapApiCrudInterface
{
    /**
     * Create item.
     *
     * @return bool
     */
    public function handleCreateRequest();

    /**
     * Get paginated items list.
     *
     * @param int $page
     * @param int $limit
     * @return \Traversable
     */
    public function handleGetListRequest($page, $limit);

    /**
     * Get item by identifier.
     *
     * @param mixed $id
     * @return object
     */
    public function handleGetRequest($id);

    /**
     * Update item.
     *
     * @param mixed $id
     * @return bool
     */
    public function handleUpdateRequest($id);

    /**
     * Delete item.
     *
     * @param mixed $id
     * @return bool
     */
    public function handleDeleteRequest($id);
}
