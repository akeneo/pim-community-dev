<?php

namespace Akeneo\Platform\Component\Tenant\Domain;

use Akeneo\Platform\Component\Tenant\Domain\Exception\TenantContextNotFoundException;

/**
 * The context store contains all tenants context
 *
 * @author  JM Leroux <jmleroux.pro@gmail.com>
 */
interface ContextStoreInterface
{
    /**
     * Returns document data as an array.
     *
     * @throws TenantContextNotFoundException
     */
    public function findDocumentById(string $documentId): array;
}
