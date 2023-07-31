<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2023 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AkeneoTestEnterprise\Platform\Integration\Tenant;

use Akeneo\Platform\Component\Tenant\Domain\ContextStoreInterface;
use Akeneo\Platform\Component\Tenant\Domain\Exception\TenantContextNotFoundException;

final class InMemoryContextStore implements ContextStoreInterface
{
    /**
     * @var array[]
     */
    private array $documents = [];

    public function findDocumentById(string $documentId): array
    {
        if (!isset($this->documents[$documentId])) {
            throw new TenantContextNotFoundException(
                sprintf('Unable to fetch context for the "%s" tenant ID: the document does not exist', $documentId)
            );
        }

        return $this->documents[$documentId];
    }

    public function addDocument(string $documentId, array $document): void
    {
        $this->documents[$documentId] = $document;
    }
}
