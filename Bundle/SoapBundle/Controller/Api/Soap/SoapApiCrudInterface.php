<?php

namespace Oro\Bundle\SoapBundle\Controller\Api\Soap;

interface SoapApiCrudInterface extends SoapApiReadInterface
{
    /**
     * Create item.
     *
     * @return bool
     */
    public function handleCreateRequest();

    /**
     * Delete item.
     *
     * @param  mixed $id
     * @return bool
     */
    public function handleDeleteRequest($id);

    /**
     * Update item.
     *
     * @param  mixed $id
     * @return bool
     */
    public function handleUpdateRequest($id);
}
