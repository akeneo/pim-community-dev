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
     * Get item by identifier.
     *
     * @param mixed $id
     * @return object
     */
    public function handleGetRequest($id);

    /**
     * Get paginated items list.
     *
     * @param int $page
     * @param int $limit
     * @return \Traversable
     */
    public function handleGetListRequest($page, $limit);

    /**
     * Delete item.
     *
     * @param mixed $id
     * @return bool
     */
    public function handleDeleteRequest($id);

    /**
     * Update item.
     *
     * @param mixed $id
     * @return bool
     */
    public function handleUpdateRequest($id);
}
